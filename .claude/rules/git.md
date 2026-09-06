# Git / GitHub Rules

Before commit:
1. `git status`
2. `git diff --check`
3. review `git diff`
4. run relevant verification
5. stage only intended files
6. review staged diff

Never commit secrets, `wp-config.php`, DB dumps, backups, uploads, release archives or browser auth state.

Commit messages must be short, clear and natural, usually one line:
- `feat: add homepage course grid`
- `fix: correct mobile menu spacing`
- `chore: configure local wordpress workflow`

No long AI-style summaries. No Claude/AI attribution trailers. Do not blindly use `git add .`. Do not push/force-push/merge/change repository settings without authorization.
