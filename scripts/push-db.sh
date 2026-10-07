#!/usr/bin/env bash
# Push the local database to Pressable, overwriting production content.
# Runs immediately (no confirmation prompt). Does not touch media — see push-media.sh.
set -euo pipefail
source "$(dirname "${BASH_SOURCE[0]}")/lib/env.sh"

CLI_CONTAINER=$(find_container cli)

echo "⚠️  This OVERWRITES the live database at ${PRESSABLE_SITE_URL} with your local content."
echo "    Run pull-content.sh first if you haven't recently, so you're not clobbering newer production changes."

TMP_SQL="$(mktemp -t astara-push-XXXXXX.sql)"
trap 'rm -f "$TMP_SQL"' EXIT

echo "==> Exporting local database with URLs rewritten for production..."
npx wp-env run cli wp search-replace "${LOCAL_URL}" "${PRESSABLE_SITE_URL}" --all-tables --export=/tmp/push-content.sql
docker cp "${CLI_CONTAINER}:/tmp/push-content.sql" "$TMP_SQL"

echo "==> Importing into the remote database..."
"${SSH_CMD[@]}" "wp --path='${PRESSABLE_WP_ROOT}' db import -" < "$TMP_SQL"
"${SSH_CMD[@]}" "wp --path='${PRESSABLE_WP_ROOT}' cache flush"

echo "✔ Pushed local database to production."
