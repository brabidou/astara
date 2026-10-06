#!/usr/bin/env bash
# Export the current local wp-env content into seed/content.xml + seed/media/ —
# a small, versioned snapshot any fresh environment (new machine, CI, cloud
# sandbox) can import with scripts/seed-content.sh, with no Pressable access
# and no Docker-independent network calls required.
#
# Run this against local dev after pulling real content (pull-content.sh) and
# trimming it down — do NOT commit a full, unreviewed copy of production data.
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$REPO_ROOT"

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

mkdir -p seed/media

echo "==> Exporting content (posts, pages, templates/parts, menus, taxonomies)..."
npx wp-env run cli wp export --dir=/tmp --filename_format=seed-export
docker cp "${WP_CONTAINER}:/tmp/seed-export.xml" seed/content.xml

echo "==> Copying uploads into seed/media/..."
docker cp "${WP_CONTAINER}:/var/www/html/wp-content/uploads/." seed/media/
find seed/media -name '.DS_Store' -delete

cat <<'EOF'

✔ Wrote seed/content.xml and seed/media/.

Before committing:
  1. Open seed/content.xml and check for anything sensitive — real customer
     names/emails in comments or authors, unpublished drafts, etc.
  2. Trim seed/media/ down to a small, representative set of images.
     This is a dev/cloud fixture, not a media library backup — keep it light.
  3. Re-run scripts/seed-content.sh against a fresh wp-env to confirm the
     seed imports cleanly before committing it.
EOF
