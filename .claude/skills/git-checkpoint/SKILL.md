---
description: Reviews and prepares a clean Git checkpoint with focused staging, verification and concise human commit messages. Use at the end of a coherent milestone/fix or when the user requests a commit.
---

# Git Checkpoint

Do not push unless separately authorized.

1. Inspect current branch, `git status`, `git diff --check`, and relevant diff.
2. Confirm applicable verification has passed or report what has not been tested.
3. Check for secrets, credentials, DB dumps, backups, logs, caches, generated artifacts and unrelated changes.
4. Stage only intended paths; avoid blind `git add .`.
5. Review staged diff.
6. Propose a clean, clear, concise one-line commit subject, preferably <=72 characters.
7. Never add AI/Claude attribution or long AI-style commit text.
8. Commit only when the task/user authorizes a commit.
9. Report the resulting commit hash when created.

Examples:
- `feat: add responsive course cards`
- `fix: correct mobile header spacing`
- `chore: add project safety rules`
- `docs: update deployment workflow`
