#!/usr/bin/env bash
# Push local content to Pressable.
#
#   ./push-db.sh          CONTENT ONLY (default): posts, pages, media records, tags
#                         and menus. Leaves the remote's users, options, active
#                         plugins and settings alone.
#   ./push-db.sh --all    the ENTIRE local database. Overwrites everything,
#                         including users, options and which plugins are active.
#
# Runs immediately (no confirmation prompt) but first saves a backup of what it's
# about to replace on the server (~/backups/). Does not touch media files; see
# push-media.sh.
set -euo pipefail
source "$(dirname "${BASH_SOURCE[0]}")/lib/env.sh"

MODE=content
if [ "${1:-}" = "--all" ]; then
	MODE=all
fi

CLI_CONTAINER=$(find_container cli)

# WordPress core lives outside the web root on Pressable, so `wp --path=<web root>`
# fails there. Running wp from inside the web root works.
remote_wp() {
	"${SSH_CMD[@]}" "cd '${PRESSABLE_WP_ROOT}' && wp $*"
}

REMOTE_PREFIX=$(remote_wp db prefix | tail -1 | tr -d '[:space:]')
LOCAL_PREFIX=$(npx wp-env run cli wp eval 'global $wpdb; echo $wpdb->prefix;' 2>/dev/null | grep -oE '[A-Za-z0-9_]+_$' | tail -1)
if [ -z "$REMOTE_PREFIX" ] || [ "$REMOTE_PREFIX" != "$LOCAL_PREFIX" ]; then
	echo "Table prefix mismatch (local '${LOCAL_PREFIX}', remote '${REMOTE_PREFIX}'). Aborting." >&2
	exit 1
fi

if [ "$MODE" = "all" ]; then
	echo "⚠️  Pushing the ENTIRE local database to ${PRESSABLE_SITE_URL}: users, options and active plugins too."
	TABLE_ARGS=(--all-tables)
	TABLES_CSV=""
else
	TABLES=(posts postmeta terms term_taxonomy term_relationships termmeta)
	TABLE_ARGS=()
	CSV=()
	for t in "${TABLES[@]}"; do
		TABLE_ARGS+=("${LOCAL_PREFIX}${t}")
		CSV+=("${LOCAL_PREFIX}${t}")
	done
	TABLES_CSV=$(IFS=,; echo "${CSV[*]}")
	echo "Pushing local CONTENT (posts, pages, media records, tags, menus) to ${PRESSABLE_SITE_URL}."
	echo "    Remote users, options, plugins and settings are left alone."
fi

TMP_SQL="$(mktemp -t astara-push-XXXXXX.sql)"
trap 'rm -f "$TMP_SQL"' EXIT

echo "==> Backing up what's about to be replaced on the server (~/backups/)..."
BACKUP="backups/pre-push-$(date +%Y%m%d-%H%M%S).sql"
remote_wp "db export \"\$HOME/${BACKUP}\" ${TABLES_CSV:+--tables=${TABLES_CSV}}" >/dev/null 2>&1 || {
	"${SSH_CMD[@]}" "mkdir -p \"\$HOME/backups\""
	remote_wp "db export \"\$HOME/${BACKUP}\" ${TABLES_CSV:+--tables=${TABLES_CSV}}"
}
echo "    saved as ~/${BACKUP}"

echo "==> Exporting local data with URLs rewritten for production..."
npx wp-env run cli wp search-replace "${LOCAL_URL}" "${PRESSABLE_SITE_URL}" "${TABLE_ARGS[@]}" --export=/tmp/push-content.sql
docker cp "${CLI_CONTAINER}:/tmp/push-content.sql" "$TMP_SQL"

echo "==> Importing into the remote database..."
"${SSH_CMD[@]}" "cd '${PRESSABLE_WP_ROOT}' && wp db import -" < "$TMP_SQL"
remote_wp cache flush
remote_wp rewrite flush

echo "✔ Pushed local ${MODE} to production. Backup: ~/${BACKUP}"
