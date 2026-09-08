#!/usr/bin/env bash
# One-command first-run local setup for a fresh clone: brings up Docker,
# installs WordPress core if needed, then hands off to
# tools/05-bootstrap.php for everything else (plugins, parent theme,
# media import, page content). See README-SETUP.md "Working with a
# teammate". Safe to re-run — every step it touches is idempotent.
#
# PowerShell/Windows equivalent: local/bootstrap.ps1 (keep both in sync).
#
# Usage: ./local/bootstrap.sh

set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$repo_root"

echo '=== Starting Docker services ==='
docker compose -f local/docker-compose.yml up -d

if [ ! -f local/.env ]; then
  echo 'local/.env not found - copying from local/.env.example (see README-SETUP.md 2.1 to customize).'
  cp local/.env.example local/.env
fi

# WP_ADMIN_* is not in the wpcli service's own `environment:` block in
# docker-compose.yml (only WORDPRESS_DB_* is) — Docker Compose substitutes
# ${VAR} into the YAML at parse time, but nothing forwards WP_ADMIN_* into
# the container's process environment, so `wp core install` never sees it
# unless passed explicitly on the command line, read from local/.env here.
admin_user="$(grep -E '^WP_ADMIN_USER=' local/.env | tail -1 | cut -d= -f2-)"
admin_pass="$(grep -E '^WP_ADMIN_PASSWORD=' local/.env | tail -1 | cut -d= -f2-)"
admin_email="$(grep -E '^WP_ADMIN_EMAIL=' local/.env | tail -1 | cut -d= -f2-)"
admin_user="${admin_user:-admin}"
admin_pass="${admin_pass:-change-me-locally}"
admin_email="${admin_email:-admin@example.test}"

echo '=== Waiting for WordPress + the database to be reachable ==='
ready=0
for _ in $(seq 1 20); do
  set +e
  docker compose -f local/docker-compose.yml run --rm wpcli core is-installed >/dev/null 2>&1
  status=$?
  set -e
  if [ "$status" -eq 0 ] || [ "$status" -eq 1 ]; then
    ready=1
    break
  fi
  sleep 3
done
if [ "$ready" -ne 1 ]; then
  echo 'WordPress/database did not become reachable in time. Check "docker compose -f local/docker-compose.yml logs".' >&2
  exit 1
fi

set +e
docker compose -f local/docker-compose.yml run --rm wpcli core is-installed >/dev/null 2>&1
installed_status=$?
set -e

if [ "$installed_status" -eq 0 ]; then
  echo 'WordPress core already installed.'
else
  echo '=== Installing WordPress core ==='
  docker compose -f local/docker-compose.yml run --rm wpcli core install \
    --url=http://localhost --title="Easy Quran Classes" \
    --admin_user="$admin_user" --admin_password="$admin_pass" --admin_email="$admin_email" --skip-email
fi

echo '=== Plugins, theme, media, pages (tools/05-bootstrap.php) ==='
docker compose -f local/docker-compose.yml run --rm wpcli --user=1 eval-file /tools/05-bootstrap.php

echo ''
echo "Done. Site: http://localhost/   Admin: http://localhost/wp-admin/  (user: $admin_user)"
