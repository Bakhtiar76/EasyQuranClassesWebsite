---
name: backup-verify
description: Verify local and cPanel rollback readiness before major milestones or deployment.
---
# Backup Verify

Local:
- confirm DB export/snapshot: `docker compose -f local/docker-compose.yml --env-file local/.env run --rm wpcli db export /backups/<name>.sql` (writes to `local/backups/`, gitignored);
- confirm required files/uploads backup;
- record timestamp/location outside Git;
- prove restore path is understood (`wpcli db import /backups/<name>.sql`).

Production/cPanel:
- require filesystem backup and DB backup before replacement/import;
- record timestamp/mechanism/restore route;
- never overwrite/delete the last known-good backup.

A backup file existing is not enough; verify it is recent and non-empty.
