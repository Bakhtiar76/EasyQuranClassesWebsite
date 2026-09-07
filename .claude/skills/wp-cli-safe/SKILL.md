---
name: wp-cli-safe
description: Use WP-CLI safely for the local WordPress development site and release exports.
---
# WP-CLI Safe

- Local stack is Docker; there is no bare `wp` binary on this machine. Run every command as:
  `docker compose -f local/docker-compose.yml --env-file local/.env run --rm wpcli <args>`
  (or `local/wp.ps1 <args>` from PowerShell). See `local/README.md`.
- Confirm local target/path before every write.
- Start with read-only commands.
- Do not print DB credentials.
- For destructive/mass changes: backup, dry-run where supported, then approval.
- Never run WP-CLI against production for this hosting account; no remote shell exists.
- For release URL conversion, prefer `wpcli search-replace OLD NEW --all-tables-with-prefix --skip-columns=guid --export=/backups/FILE.sql` after a dry-run so serialized data is handled and the working DB is not mutated. `/backups` is the container path bound to `local/backups/`.
- Store exports outside Git.
