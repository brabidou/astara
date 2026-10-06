#!/bin/bash
# Cloud-session bootstrap: deps, linter, Docker daemon, Node proxy support.
# Does NOT start wp-env — see README/notes: its image build needs network
# egress (apt over HTTP, Docker Hub) that the default cloud policy blocks.
set -euo pipefail

if [ "${CLAUDE_CODE_REMOTE:-}" != "true" ]; then
  exit 0
fi

cd "${CLAUDE_PROJECT_DIR:-.}"

# Node's got/fetch ignore HTTPS_PROXY unless told to; wp-env otherwise 403s.
if [ -n "${CLAUDE_ENV_FILE:-}" ]; then
  echo 'export NODE_USE_ENV_PROXY=1' >> "$CLAUDE_ENV_FILE"
  echo 'export COMPOSER_ALLOW_SUPERUSER=1' >> "$CLAUDE_ENV_FILE"
fi
export COMPOSER_ALLOW_SUPERUSER=1

# npm deps (wp-env, Playwright). install, not ci, so the cached container is reused.
npm install --no-audit --no-fund

# PHPCS + WordPress Coding Standards. Superuser flag is required or the
# installer plugin is skipped and phpcs reports "No sniffs were registered".
composer install --no-interaction --no-progress

# Docker daemon isn't running by default in the container.
if command -v dockerd >/dev/null 2>&1 && ! docker info >/dev/null 2>&1; then
  (nohup dockerd >/tmp/dockerd.log 2>&1 &)
  for _ in $(seq 1 30); do
    docker info >/dev/null 2>&1 && break
    sleep 1
  done
fi
