# Easy Quran Classes — Claude Code Starter Pack

## Confirmed project workflow

The client's cPanel hosting has **no Shell/SSH access**.

Development therefore happens locally:

`Local WordPress + Claude Code + Elementor + WP-CLI + Playwright -> release package -> manual cPanel deployment`

Do not configure remote SSH/WP-CLI/rsync workflows unless hosting access changes later.

## Files

- `TASK.md` — one-time Claude Code/local WordPress environment setup task
- `DESIGN.md` — visual source of truth
- `CLAUDE.md` — persistent project instructions
- `CPANEL-WORKFLOW.md` — local-to-cPanel deployment and rollback workflow
- `.claude/rules/` — focused project rules
- `.claude/skills/` — reusable WordPress/Elementor/QA/deployment workflows
- `.gitignore` — starting ignore rules; review against the actual repository

## Recommended Placement

Place this pack at the root of the Easy Quran Classes Git repository.

Do not place the repository in a directory containing unrelated client credentials or private files.

## First Claude Code Run

Start Claude Code from the repository root and tell it:

```text
Read CLAUDE.md, DESIGN.md, CPANEL-WORKFLOW.md and TASK.md fully.
Execute TASK.md from Phase A.
This project has no cPanel shell/SSH access, so establish a local WordPress development environment and do not attempt remote shell workflows.
Do not start page implementation until the setup task is verified.
```

## Local WordPress Environment

Claude should audit what is already installed before adding anything.

Preferred order:
1. existing healthy local WordPress environment;
2. Docker Desktop + Docker Compose;
3. LocalWP;
4. XAMPP/WAMP fallback.

Use only one primary stack.

## Browser QA

When Node.js 20+ is confirmed, Playwright CLI is preferred for local browser automation/visual QA.

Suggested installation after audit/approval:

```bash
npm install -g @playwright/cli@latest
playwright-cli install --skills
```

Browser automation should target localhost during development. Do not save WordPress/cPanel authentication state in Git.

## WP-CLI

WP-CLI is required locally when the chosen stack supports it.

Use it for:
- local inspection;
- plugin/theme operations;
- cache operations;
- database backups;
- serialization-safe production URL export.

It is **not available remotely on this cPanel account**.

## cPanel

cPanel is used manually for:
- backups;
- File Manager upload/extract;
- database creation;
- phpMyAdmin import;
- production PHP/SSL checks;
- final smoke testing.

Read `CPANEL-WORKFLOW.md` before packaging a release.

## MCP Policy

Start with no third-party MCP servers.

Do not add generic cPanel, SSH, filesystem or database MCP servers. They are unnecessary for the approved no-shell deployment model.

## Git

Git stores custom code, Claude configuration and docs — not the Elementor/WordPress database or release packages.

Commit messages must remain short, clear and natural, with no AI/Claude attribution.
