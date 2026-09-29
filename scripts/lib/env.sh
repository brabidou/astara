#!/usr/bin/env bash
# Shared helpers for content sync scripts. Sourced, not executed directly.
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$REPO_ROOT"

if [ -f .env ]; then
	set -a
	# shellcheck disable=SC1091
	source .env
	set +a
fi

require_var() {
	local name="$1"
	if [ -z "${!name:-}" ]; then
		echo "Missing required .env value: ${name}" >&2
		echo "Copy .env.example to .env and fill it in first." >&2
		exit 1
	fi
}

require_var PRESSABLE_SFTP_HOST
require_var PRESSABLE_SFTP_USER
require_var PRESSABLE_SFTP_PORT
require_var PRESSABLE_WP_ROOT
require_var PRESSABLE_SITE_URL

LOCAL_URL="http://localhost:8888"
SSH_CMD=(ssh -p "${PRESSABLE_SFTP_PORT}" "${PRESSABLE_SFTP_USER}@${PRESSABLE_SFTP_HOST}")

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
