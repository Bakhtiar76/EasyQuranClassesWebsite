---
description: Verifies that usable WordPress filesystem and database backups exist and that a rollback plan is understood before risky work. Use before major upgrades, migrations, search-replace, deployment or production changes.
---

# Backup Verification

Do not restore anything in this skill.

First determine the environment and backup mechanism.

Verify, without exposing sensitive contents:

- database backup exists
- filesystem/site backup exists
- timestamp is recent enough for the planned operation
- file sizes are non-zero and plausible
- storage location is known
- backup is not unintentionally public
- restore method is known
- required access for restore is available

If no adequate backup exists, report the gap.

Creating a new production backup may consume storage and change server state, so show the proposed backup method/command and ask for approval first.

Output a GO / NO-GO recommendation for the planned operation.
