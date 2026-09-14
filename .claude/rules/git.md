# Git / GitHub Rules

## Branch Policy (confirmed 2026-09-07)

`main` is production-deployment-only. It contains **only**
`wp-content/themes/easy-quran-classes-child/` (and
`wp-content/plugins/easy-quran-classes-core/` if that plugin is ever added),
`.gitignore`, and `.github/workflows/` (the FTPS auto-deploy pipeline —
must live on `main` for GitHub Actions to trigger on push to `main`).
Nothing else — no `.claude/`, no docs (`CLAUDE.md`, `DESIGN.md`,
`CPANEL-WORKFLOW.md`, `TASK*.md`, `AGENTS.md`, `README-SETUP.md`,
`TOOL-INVENTORY.md`), no `local/`, `tools/`, `tests/`, `Assests/` or
`ref_website/`. `main`'s own `.gitignore` lists these paths so they are not
re-added by accident.

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

**If this working directory may be shared with another concurrent session**
(check `git status`/`git log` for unexpected changes appearing between two
consecutive checks — a sign another session is committing live in the same
checkout), do not `git checkout main` here: that switches the branch for
every session sharing this working tree at once and can destroy another
session's in-progress uncommitted work. Use an isolated worktree instead:
`git worktree add ../<some-name> main`, do the commit/push there, then
`git worktree remove ../<some-name>` when done.

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

## Local-only BugDrop gate

BugDrop must never exist in production code. It belongs only in
`local/mu-plugins/` on feature branches. Before copying production paths to
`main`, committing on `main`, or pushing `main`, run:

```text
git grep -n -i bugdrop main -- wp-content/themes/easy-quran-classes-child
```

The command must return no matches. Also inspect the staged/packaged child
theme for any BugDrop file, loader, worker URL, or script tag. Any match blocks
the commit, push, and deployment until the local integration has been removed
from the production tree.
