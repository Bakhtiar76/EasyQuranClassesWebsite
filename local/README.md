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

## Config

Copy `local/.env.example` to `local/.env` and adjust if needed — `local/.env` is gitignored (root `.gitignore`'s `.env` / `.env.*` patterns). These are local-only DB/admin credentials, never production values.

## Volumes

- `eqc_wp` (named volume) — WordPress core, plugins, uploads, DB-backed content. Not in Git; it's mutable local state.
- `wp-content/themes/easy-quran-classes-child/` — bind-mounted from the repo. This is the only WordPress path Claude edits directly as source code.
- `local/backups/` — bind-mounted for WP-CLI DB exports and backup archives. Gitignored (root `backups/` pattern).

## Workflow

1. `docker compose -f local/docker-compose.yml up -d`
2. Confirm `http://localhost/` and `/wp-admin/` load.
3. Build/edit in Elementor via the browser, or through WP-CLI/Novamira where reliable.
4. Visual QA: Playwright sweep across the `DESIGN.md` §21 viewports, plus `chrome-devtools` MCP for interactive inspection and Lighthouse.
5. Checkpoint before any risky change: `wp db export /backups/<name>.sql` and `wp media export` / uploads copy as needed.

See `../CPANEL-WORKFLOW.md` for the release/export process and `../.claude/skills/` for the guided workflows (`wp-cli-safe`, `wp-audit`, `release-check`, `backup-verify`, etc).

## Novamira (local-only WordPress MCP)

Installed and active — local site only, never on production. Two independent connection paths exist:

- **MCP (`.mcp.json` at repo root, working)** — Claude Code connects directly via `@automattic/mcp-wordpress-remote`. The real Application Password lives only in `local/.env` (`NOVAMIRA_APP_PASSWORD`, gitignored); `.mcp.json` references it as `${NOVAMIRA_APP_PASSWORD}`. For this to resolve, that value must also exist as a real Windows **User** environment variable (Claude Code's `${VAR}` substitution reads the OS environment, not `local/.env`, directly) — see `local/.env` for the value, set it with `[Environment]::SetEnvironmentVariable(...)`, then restart Claude Code.
- **Novamira CLI (`novamira` binary, not connected)** — its own `auth login` flow stores a session in the Windows Credential Manager, which isn't reachable from Claude Code's sandboxed tool-execution context (`Error: The OS credential service could not complete the operation`). Run `novamira auth login 'http://localhost/'` from a normal interactive terminal (not through Claude Code) if this path is ever needed; the MCP path above already covers the same abilities for Claude Code's own use.

Requires `WP_ENVIRONMENT_TYPE=local` (set in `docker-compose.yml`'s `WORDPRESS_CONFIG_EXTRA`) — WordPress core itself refuses Application Passwords/OAuth over plain HTTP without it.
