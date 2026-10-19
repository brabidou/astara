#!/usr/bin/env bash
# Pull the Pressable database and media into the local wp-env site.
#
# Overwrites local content (database and uploads). Never writes to Pressable:
# every remote command is a read (export) or a download (rsync from remote).
#
# Before importing, the current local database is saved to
# ~/backups/local-before-pull-<timestamp>.sql, so a pull never loses local work.
# Restore it with:
#   docker cp ~/backups/<file>.sql <cli container>:/tmp/restore.sql
#   npx wp-env run cli wp db import /tmp/restore.sql
set -euo pipefail
source "$(dirname "${BASH_SOURCE[0]}")/lib/env.sh"

CLI_CONTAINER=$(find_container cli)
WP_CONTAINER=$(find_container wordpress)

STAMP="$(date +%Y%m%d-%H%M%S)"
BACKUP_DIR="${HOME}/backups"
LOCAL_BACKUP="${BACKUP_DIR}/local-before-pull-${STAMP}.sql"

TMP_SQL="$(mktemp -t astara-pull-XXXXXX)"
TMP_UPLOADS="$(mktemp -d -t astara-uploads-XXXXXX)"
trap 'rm -f "$TMP_SQL"; rm -rf "$TMP_UPLOADS"' EXIT

echo "⚠️  This replaces your LOCAL database and uploads with ${PRESSABLE_SITE_URL}."
echo "    Pressable is only read from, never changed."

echo "==> Backing up the local database first..."
mkdir -p "$BACKUP_DIR"
npx wp-env run cli wp db export /tmp/local-before-pull.sql >/dev/null
docker cp "${CLI_CONTAINER}:/tmp/local-before-pull.sql" "$LOCAL_BACKUP"
echo "    saved as ${LOCAL_BACKUP}"

echo "==> Exporting the remote database with URLs rewritten for local..."
# WordPress core lives outside the web root on Pressable, so `wp --path=<web root>`
# fails; run wp from inside the web root instead. --export only reads.
# --no-report drops the replacements table; WP-CLI still prints one
# "Success: Made N replacements" line into the stream, which is removed here.
if ! "${SSH_CMD[@]}" "cd '${PRESSABLE_WP_ROOT}' && wp search-replace '${PRESSABLE_SITE_URL}' '${LOCAL_URL}' --all-tables --no-report --export=-" > "$TMP_SQL"; then
	echo "✖ The remote export failed:" >&2
	head -n 5 "$TMP_SQL" | cut -c1-200 >&2
	echo "  Local was not touched." >&2
	exit 1
fi
sed -i '' -e '/^Success: Made [0-9]* replacements/d' "$TMP_SQL"

if [ ! -s "$TMP_SQL" ]; then
	echo "✖ The remote export came back empty (SSH dropped?). Local was not touched." >&2
	exit 1
fi
# The export must be pure SQL: anything else (a WP-CLI table or warning) on the
# first line makes MySQL abort the whole import.
if ! head -c 4096 "$TMP_SQL" | grep -qE '^(--|/\*|DROP |CREATE |INSERT |SET )'; then
	echo "✖ The remote export doesn't look like SQL. First line:" >&2
	head -n 1 "$TMP_SQL" | cut -c1-200 >&2
	echo "  Local was not touched." >&2
	exit 1
fi

echo "==> Importing into the local database..."
docker cp "$TMP_SQL" "${CLI_CONTAINER}:/tmp/pull-content.sql"
# wp db import can print a MySQL ERROR yet still report success, so check the output.
IMPORT_LOG="$(npx wp-env run cli wp db import /tmp/pull-content.sql 2>&1 || true)"
if echo "$IMPORT_LOG" | grep -q "ERROR"; then
	echo "$IMPORT_LOG" | grep "ERROR" | cut -c1-300 >&2
	echo "✖ The import failed. Restore your local database from ${LOCAL_BACKUP} if anything looks wrong." >&2
	exit 1
fi

echo "==> Downloading uploads..."
rsync -az -e "ssh -p ${PRESSABLE_SFTP_PORT}" \
	"${PRESSABLE_SFTP_USER}@${PRESSABLE_SFTP_HOST}:${PRESSABLE_WP_ROOT}/wp-content/uploads/" \
	"${TMP_UPLOADS}/"

echo "==> Copying uploads into the local site..."
docker cp "${TMP_UPLOADS}/." "${WP_CONTAINER}:/var/www/html/wp-content/uploads/"
# docker cp writes files as root; hand them to the web server user so local
# WordPress can edit, crop and regenerate them.
docker exec "$WP_CONTAINER" chown -R www-data:www-data /var/www/html/wp-content/uploads

echo "==> Flushing caches and permalinks..."
npx wp-env run cli wp rewrite flush >/dev/null
npx wp-env run cli wp cache flush >/dev/null

echo "✔ Pulled ${PRESSABLE_SITE_URL} content and media into local dev."
echo "  Previous local database: ${LOCAL_BACKUP}"
echo "  Note: the local admin password is now the same as on ${PRESSABLE_SITE_URL}."
