# Contributing — Git Workflow

Read this before your first `git push`. Full environment setup is in `README-SETUP.md`;
this file is just the day-to-day push/PR rule.

## The one rule that matters

**Never push directly to `main`.** `main` is production — a push to it auto-deploys the
child theme to the live site via GitHub Actions (`.github/workflows/deploy.yml`). This isn't
currently enforced by GitHub itself (Private repo limitation). Treat it as
a hard rule, not a suggestion.

## Day-to-day workflow

1. **Start from `feature/setup`**, always up to date:
   ```bash
   git checkout feature/setup
   git pull
   ```
2. **Branch off it** for whatever you're working on:
   ```bash
   git checkout -b feature/short-topic-name
   ```
3. **Commit as you go.** Short, plain messages — no AI-generated summaries, no
   `Co-Authored-By` trailers:
   ```
   fix: correct mobile nav spacing
   feat: add pricing FAQ section
   ```
4. **Push your branch** (never `feature/setup` or `main` directly):
   ```bash
   git push -u origin feature/short-topic-name
   ```
5. **Open a Pull Request on GitHub**, base branch `feature/setup` (not `main`):
   - Title: what changed, in one line.
   - Description: what you did and why, and how you checked it (screenshot, page loaded,
     `tests/visual/sweep.mjs` passed, etc.).
6. **Get it reviewed before merging** — even a quick look from the other person. Don't merge
   your own PR the same minute you opened it.
7. **Merge via the GitHub "Merge pull request" button** (Squash and merge is fine — keeps
   `feature/setup`'s history readable). Don't merge locally with `git merge` and push that.
8. **Delete the branch** after merging (GitHub offers a button for this on the PR page).

## Releasing to `main`

Don't release to main, if a change
needs to go live, say so in your PR description; don't push it to `main` yourself.

## Never

- Never push to `main`.
- Never force-push a shared branch (`feature/setup` or anyone else's branch).
- Never commit `.env`, `wp-config.php`, database dumps, or anything under `local/backups/`.
- Never `git checkout main` in a shared working copy — it can yank the branch out from under
  someone else's uncommitted work. Use your own clone/worktree.
- Never merge your own PR without at least a quick review from someone else, if they're
  available.
