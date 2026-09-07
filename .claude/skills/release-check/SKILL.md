---
name: release-check
description: Prepare and validate a manual cPanel-ready WordPress release package without deploying it automatically.
---
# Release Check

1. Confirm local QA complete and Git diff reviewed.
2. Confirm exact production URL and target state.
3. Create local DB/files backup.
4. Clean caches/temp/debug artifacts.
5. Generate production-URL SQL with serialization-safe WP-CLI export after dry-run:
   `docker compose -f local/docker-compose.yml --env-file local/.env run --rm wpcli search-replace 'http://localhost' '<production-url>' --all-tables-with-prefix --skip-columns=guid --export=/backups/release-production.sql`
6. Package required site files without secrets/backups/local auth state.
7. **Hard check — fail the release if it does not pass:** confirm both `novamira` and `vibe-ai` (WPVibe) plugin directories are absent from the packaged `wp-content/plugins/` tree (`local/wp.ps1 plugin list` should not show either, active or otherwise, and neither may appear in the archive). Both are local-development-only AI-agent connectors (Novamira: arbitrary PHP execution + full DB/filesystem access; WPVibe: a hosted cloud relay to `mcp.wpvibe.ai`) and must never reach production.
8. Produce a manifest: archive name/size, SQL name/size, source commit, production URL, deployment mode, rollback prerequisites.
9. Verify archive structure locally.
10. Do not upload/deploy automatically.
11. Give user cPanel File Manager/phpMyAdmin steps from `CPANEL-WORKFLOW.md`.
