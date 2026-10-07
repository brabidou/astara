#!/usr/bin/env bash
# Pull the Pressable database and media into the local wp-env site.
# Overwrites local content. Never touches production.
set -euo pipefail
source "$(dirname "${BASH_SOURCE[0]}")/lib/env.sh"

CLI_CONTAINER=$(find_container cli)
WP_CONTAINER=$(find_container wordpress)

TMP_SQL="$(mktemp -t astara-pull-XXXXXX.sql)"
trap 'rm -f "$TMP_SQL"' EXIT

echo "==> Exporting remote database with URLs rewritten for local..."
# WordPress core lives outside the web root on Pressable, so `wp --path=<web root>` fails;
# run wp from inside the web root instead.
"${SSH_CMD[@]}" "cd '${PRESSABLE_WP_ROOT}' && wp search-replace '${PRESSABLE_SITE_URL}' '${LOCAL_URL}' --all-tables --export=-" > "$TMP_SQL"

echo "==> Importing into local database..."
docker cp "$TMP_SQL" "${CLI_CONTAINER}:/tmp/pull-content.sql"
npx wp-env run cli wp db import /tmp/pull-content.sql
npx wp-env run cli wp cache flush

echo "==> Syncing uploads..."
TMP_UPLOADS="$(mktemp -d -t astara-uploads-XXXXXX)"
trap 'rm -f "$TMP_SQL"; rm -rf "$TMP_UPLOADS"' EXIT
rsync -az --delete -e "ssh -p ${PRESSABLE_SFTP_PORT}" \
	"${PRESSABLE_SFTP_USER}@${PRESSABLE_SFTP_HOST}:${PRESSABLE_WP_ROOT}/wp-content/uploads/" \
	"${TMP_UPLOADS}/"
docker cp "${TMP_UPLOADS}/." "${WP_CONTAINER}:/var/www/html/wp-content/uploads/"

echo "✔ Pulled production content and media into local dev."
