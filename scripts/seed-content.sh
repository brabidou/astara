#!/usr/bin/env bash
# Import the committed content seed (seed/content.xml + seed/media/) into local
# wp-env. Use this on a fresh environment with no Pressable access — a new
# machine, CI, or a cloud sandbox without Docker-based wp-env and SSH creds
# aren't required here, just a running wp-env.
#
# This is NOT the same as pull-content.sh: that pulls real, current production
# content over SSH; this replays a small, versioned, sanitized fixture from git.
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$REPO_ROOT"

if [ ! -f seed/content.xml ]; then
	echo "seed/content.xml not found — run scripts/export-seed.sh against a populated site first." >&2
	exit 1
fi

find_container() {
	local suffix="$1"
	local name
	name=$(docker ps --format '{{.Names}}' | grep -E "wp-env-.*-${suffix}-1\$" | head -1 || true)
	if [ -z "$name" ]; then
		echo "Could not find the wp-env '${suffix}' container — is 'npx wp-env start' running?" >&2
		exit 1
	fi
	echo "$name"
}

WP_CONTAINER=$(find_container wordpress)

echo "==> Copying seed media into uploads..."
docker cp seed/media/. "${WP_CONTAINER}:/var/www/html/wp-content/uploads/"

echo "==> Ensuring the WordPress Importer is available (wp import needs it)..."
npx wp-env run cli wp plugin is-active wordpress-importer >/dev/null 2>&1 \
	|| npx wp-env run cli wp plugin install wordpress-importer --activate

echo "==> Importing seed content..."
docker cp seed/content.xml "$(find_container cli):/tmp/seed-content.xml"
npx wp-env run cli wp import /tmp/seed-content.xml --authors=create

echo "==> Flushing caches and rewrite rules..."
npx wp-env run cli wp cache flush
npx wp-env run cli wp rewrite flush

echo "✔ Imported seed content and media into local dev."
