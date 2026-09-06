---
name: release-check
description: Prepare and validate a manual cPanel-ready WordPress release package without deploying it automatically.
---
# Release Check

1. Confirm local QA complete and Git diff reviewed.
2. Confirm exact production URL and target state.
3. Create local DB/files backup.
4. Clean caches/temp/debug artifacts.
5. Generate production-URL SQL with serialization-safe WP-CLI export after dry-run.
6. Package required site files without secrets/backups/local auth state.
7. Produce a manifest: archive name/size, SQL name/size, source commit, production URL, deployment mode, rollback prerequisites.
8. Verify archive structure locally.
9. Do not upload/deploy automatically.
10. Give user cPanel File Manager/phpMyAdmin steps from `CPANEL-WORKFLOW.md`.
