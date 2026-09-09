# Local WordPress — Docker

Local-only development stack for Easy Quran Classes. Never point this at production.

## Start / stop

```bash
docker compose -f local/docker-compose.yml up -d
docker compose -f local/docker-compose.yml ps
docker compose -f local/docker-compose.yml down        # stop, keep data
docker compose -f local/docker-compose.yml down -v      # stop and wipe data (ask first)
```

Site: http://localhost · Admin: http://localhost/wp-admin/

**The port mapping must stay `80:80`.** Inside the container WordPress calls its own
`home_url()`; with a mismatched mapping like `8080:80` that becomes `localhost:8080`, which
resolves to nothing inside the container (only Docker's host-side proxy understands it). That
silently breaks WordPress self-loopback, Novamira's REST self-check (`cURL error 7`) and
WP-Cron. See `../CLAUDE.md` "Local Environment".

## WP-CLI

Run any WP-CLI command through the one-shot `wpcli` service:

```bash
docker compose -f local/docker-compose.yml run --rm wpcli plugin list
docker compose -f local/docker-compose.yml run --rm wpcli theme list
```

Or the PowerShell wrapper:

```powershell
.\local\wp.ps1 plugin list
.\local\wp.ps1 theme list
```

From **Git Bash**, prefix any command with a `/`-leading argument with `MSYS_NO_PATHCONV=1` —
otherwise MSYS rewrites e.g. `/backups/x.sql` into `C:/Program Files/Git/backups/x.sql`:

```bash
MSYS_NO_PATHCONV=1 docker compose -f local/docker-compose.yml --env-file local/.env \
  run --rm wpcli db export /backups/checkpoint.sql
```

## Config

Copy `local/.env.example` to `local/.env` and adjust if needed — `local/.env` is gitignored (root `.gitignore`'s `.env` / `.env.*` patterns). These are local-only DB/admin credentials, never production values.

**Never share your `local/.env` with a teammate, and never expect theirs to match yours.** Each
person's local WordPress is a fully independent install — everyone copies `local/.env.example` and
picks their own `WP_ADMIN_PASSWORD`; it only has to work on their own machine. `NOVAMIRA_APP_PASSWORD`
is a WordPress Application Password, which WordPress ties to one specific site install — it cannot
be copied between machines either; each person generates their own (README-SETUP.md §5).

`WP_ADMIN_USER` / `_PASSWORD` / `_EMAIL` are used by `wp core install`, which creates the local wp-admin account on first bring-up (change the password any time afterward with `wp user update admin --user_pass=...`). This step is now scripted — see "First-run bootstrap" below — so there is no need to run it by hand.

## First-run bootstrap

On a fresh clone (no WordPress data yet), one command does everything: start Docker, install WordPress core, install/activate plugins and the parent theme, import media from `local/media-staging/`, then run every `tools/00-04*.php` and `tools/pages/*.php` script.

```powershell
.\local\bootstrap.ps1
```

```bash
./local/bootstrap.sh
```

Both are idempotent — safe to re-run after `docker compose down -v`, or any time to pick up new pages/media added since the last run. See `../README-SETUP.md` §3 for the full first-run procedure and §"Working with a teammate" for the multi-machine workflow this replaces.

## Volumes

- `eqc_wp` (named volume) — WordPress core, plugins, uploads, DB-backed content. Not in Git; it's mutable local state.
- `wp-content/themes/easy-quran-classes-child/` — bind-mounted from the repo. This is the only WordPress path Claude edits directly as source code.
- `local/backups/` — bind-mounted for WP-CLI DB exports and backup archives. Gitignored (root `backups/` pattern).

## Workflow

1. `docker compose -f local/docker-compose.yml up -d`
2. Confirm `http://localhost/` and `/wp-admin/` load.
3. Build/edit in Elementor via the browser, or through WP-CLI/Novamira where reliable.
4. Visual QA: `node tests/visual/sweep.mjs http://localhost/` across the `DESIGN.md` viewports (project-local Playwright, not global), plus `chrome-devtools` MCP for interactive inspection and Lighthouse.
5. Checkpoint before any risky change: `MSYS_NO_PATHCONV=1 ... wpcli db export /backups/<name>.sql` and an uploads copy as needed.

See `../CPANEL-WORKFLOW.md` for the release/export process and `../.claude/skills/` for the guided workflows (`wp-cli-safe`, `wp-audit`, `release-check`, `backup-verify`, etc). Codex gets the same skills as `eqc-*` — see `../README-SETUP.md` §5.

## Novamira (local-only WordPress MCP)

Installed and active — **local site only, never on production**. Connected via **two working
paths**; full diagnosis and the ACL fix are in `../CLAUDE.md` "Local Environment" and
`../README-SETUP.md` §7.

- **MCP — `.mcp.json` (Claude Code) and `../tools/codex/novamira-mcp.cmd` (Codex).** Both use
  `@automattic/mcp-wordpress-remote`. The Application Password lives only in `local/.env`
  (`NOVAMIRA_APP_PASSWORD`, gitignored). Claude Code's `.mcp.json` reads it as
  `${NOVAMIRA_APP_PASSWORD}` from the **OS environment** — so it must also be a Windows **User**
  env var (`[Environment]::SetEnvironmentVariable('NOVAMIRA_APP_PASSWORD','<value>','User')`,
  then restart Claude Code). Codex strips `*PASSWORD*` env vars, so its wrapper reads
  `local/.env` directly.
- **Novamira CLI — `novamira --site eqc-local-file <command>`.** Connected. The default Windows
  Credential Manager backend fails on this machine (confirmed **not** sandbox-specific — same
  error in a plain terminal), so the CLI is forced onto its file backend with
  `NOVAMIRA_CREDENTIAL_BACKEND=file` (Windows User env var) plus a one-time `icacls` ACL repair
  on `%LOCALAPPDATA%\Novamira\{Credentials,Cache}`. `novamira doctor --json` then passes except
  the expected `credential.backend: warn`.

Requires `WP_ENVIRONMENT_TYPE=local` (set in `docker-compose.yml`'s `WORDPRESS_CONFIG_EXTRA`) —
WordPress core itself refuses Application Passwords / OAuth over plain HTTP without it.
