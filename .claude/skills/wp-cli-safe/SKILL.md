---
description: Plans and executes WP-CLI work with environment checks, rollback thinking and production safety gates. Use whenever a task is best handled through WP-CLI.
---

# Safe WP-CLI Workflow

Before any WP-CLI command:

1. Identify current directory and WordPress root.
2. Confirm target domain and environment.
3. Classify the command as read-only, low-impact write, or high-impact write.
4. For writes, explain affected data and rollback.

Read-only inspection may proceed when target access is already approved.

For production or high-impact writes, stop and request explicit approval first.

Never expose credentials from `wp-config.php`.

Never run database reset/drop/import, mass delete, or search/replace implicitly.

For `wp search-replace`, require:

- current DB backup
- exact old/new values
- table scope understood
- `--dry-run` first
- review of serialized-data implications
- explicit approval for the real run

After a write, run read-only verification and report what changed.
