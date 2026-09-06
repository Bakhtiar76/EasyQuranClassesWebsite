---
name: wp-cli-safe
description: Use WP-CLI safely for the local WordPress development site and release exports.
---
# WP-CLI Safe

- Confirm local target/path before every write.
- Start with read-only commands.
- Do not print DB credentials.
- For destructive/mass changes: backup, dry-run where supported, then approval.
- Never run WP-CLI against production for this hosting account; no remote shell exists.
- For release URL conversion, prefer `wp search-replace OLD NEW --all-tables-with-prefix --skip-columns=guid --export=FILE.sql` after a dry-run so serialized data is handled and the working DB is not mutated.
- Store exports outside Git.
