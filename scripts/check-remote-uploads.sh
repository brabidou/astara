#!/usr/bin/env bash
# Read-only: show where uploaded media actually lives on Pressable and whether
# it matches the folder WordPress serves uploads from. Changes nothing.
set -euo pipefail
source "$(dirname "${BASH_SOURCE[0]}")/lib/env.sh"

"${SSH_CMD[@]}" "WP_ROOT='${PRESSABLE_WP_ROOT}' bash -s" <<'REMOTE'
set +e
echo "== who / where"
whoami
pwd

echo
echo "== uploads folder under each candidate root"
for d in "$WP_ROOT" /htdocs /srv/htdocs "$HOME/htdocs"; do
	echo "-- $d/wp-content/uploads"
	ls -ld "$d/wp-content/uploads" 2>&1
	ls "$d/wp-content/uploads" 2>&1 | head -8
done

echo
echo "== where does ally.png actually live?"
find /htdocs /srv/htdocs "$HOME" -maxdepth 8 -name 'ally.png' 2>/dev/null | head

echo
echo "== is 2026/09 there, and what are the permissions?"
ls -la "$WP_ROOT/wp-content/uploads/2026/09" 2>&1 | head -8

echo
echo "== which way of calling WP-CLI works? (prints the site URL when it does)"
try() {
	echo "-- $1"
	shift
	"$@" 2>&1 | tail -2
}
try "wp (no --path), from home"            wp option get home
try "cd /htdocs && wp"                     bash -c 'cd /htdocs && wp option get home'
try "wp --path=/htdocs (what the scripts use now)" wp --path=/htdocs option get home
try "wp --path=/srv/htdocs"                wp --path=/srv/htdocs option get home

echo
echo "== WP-CLI details and where core lives"
wp --info 2>&1 | grep -iE "wp-cli (config|packages)|php binary|wp-cli version" | head -4
ls -d /wordpress/core/* 2>&1 | head -3
ls -la /srv/htdocs/wp-config.php /htdocs/wp-config.php 2>&1 | head -2
echo
echo "== WordPress's own uploads folder"
wp eval 'echo wp_get_upload_dir()["basedir"], PHP_EOL;' 2>&1 | tail -2
REMOTE
