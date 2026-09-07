# Git / GitHub Rules

## Branch Policy (confirmed 2026-09-07)

`main` is production-deployment-only. It contains **only**
`wp-content/themes/easy-quran-classes-child/` (and
`wp-content/plugins/easy-quran-classes-core/` if that plugin is ever added)
plus `.gitignore`. Nothing else — no `.claude/`, no docs (`CLAUDE.md`,
`DESIGN.md`, `CPANEL-WORKFLOW.md`, `TASK*.md`, `AGENTS.md`,
`README-SETUP.md`, `TOOL-INVENTORY.md`), no `local/`, `tools/`, `tests/`,
`Assests/` or `ref_website/`. `main`'s own `.gitignore` lists these paths so
they are not re-added by accident.

All development happens on `feature/*` branches (currently `feature/setup`),
which keep the full tree: docs, `.claude/` configuration, local dev tooling,
reference assets and tests. Do **not** `git merge` a feature branch into
`main` — a full-branch merge reintroduces every dev-only path `main` was
deliberately pruned of. To release a change, copy only the production paths
across, e.g.:

```
git checkout main
git checkout feature/setup -- wp-content/themes/easy-quran-classes-child
git commit -m "feat: <describe the change>"
```

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
