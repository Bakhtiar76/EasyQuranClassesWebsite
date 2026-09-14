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
8. **Hard check — fail the release/main push if it does not pass:** BugDrop must exist only under `local/mu-plugins/`. Run `git grep -n -i bugdrop main -- wp-content/themes/easy-quran-classes-child` and require no matches; also inspect the packaged child theme and require no BugDrop script, loader, URL, or file. Never copy `local/` to `main` or production.
9. Produce a manifest: archive name/size, SQL name/size, source commit, production URL, deployment mode, rollback prerequisites.
10. Verify archive structure locally.
11. Do not upload/deploy automatically.
12. Give user cPanel File Manager/phpMyAdmin steps from `CPANEL-WORKFLOW.md`.
