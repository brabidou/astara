#!/usr/bin/env bash
# Push local media (wp-content/uploads) up to Pressable. Additive only —
# never deletes remote files the local copy doesn't have. Requires confirmation.
set -euo pipefail
source "$(dirname "${BASH_SOURCE[0]}")/lib/env.sh"

WP_CONTAINER=$(find_container wordpress)

echo "⚠️  This uploads local media into production at ${PRESSABLE_SITE_URL}."
echo "    Existing remote files with no local match are left alone (no --delete)."
read -r -p "Type PUSH to continue: " CONFIRM
if [ "$CONFIRM" != "PUSH" ]; then
	echo "Aborted."
	exit 1
fi

TMP_UPLOADS="$(mktemp -d -t astara-uploads-push-XXXXXX)"
trap 'rm -rf "$TMP_UPLOADS"' EXIT

echo "==> Copying local uploads out of the container..."
docker cp "${WP_CONTAINER}:/var/www/html/wp-content/uploads/." "${TMP_UPLOADS}/"

# mktemp -d makes the staging folder owner-only (0700), and rsync -a copies that
# mode onto the remote uploads directory, so the web server can't read any media
# (every image 404s). Normalise to web-readable modes first. Plain chmod rather
# than rsync --chmod, which macOS's bundled rsync doesn't support.
chmod -R u=rwX,go=rX "${TMP_UPLOADS}"

echo "==> Syncing to production..."
rsync -az -e "ssh -p ${PRESSABLE_SFTP_PORT}" \
	"${TMP_UPLOADS}/" \
	"${PRESSABLE_SFTP_USER}@${PRESSABLE_SFTP_HOST}:${PRESSABLE_WP_ROOT}/wp-content/uploads/"

echo "✔ Pushed local media to production."
