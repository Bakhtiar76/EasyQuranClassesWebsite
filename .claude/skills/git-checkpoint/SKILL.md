---
name: git-checkpoint
description: Review and create a disciplined local Git checkpoint with concise human commit messages.
---
# Git Checkpoint

1. `git status`
2. `git diff --check`
3. review full diff
4. verify tests/checks are complete
5. ensure no secrets/DB dumps/backups/releases/uploads/auth state
6. stage only intended files
7. review staged diff
8. propose one concise one-line commit message
9. commit only when authorized
10. never push unless separately authorized

Never add Claude/AI attribution or long generated commit text.
