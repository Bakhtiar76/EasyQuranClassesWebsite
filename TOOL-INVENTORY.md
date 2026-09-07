# Easy Quran Classes — Tool Inventory

Update this after any local setup change. Do not record passwords, tokens, DB credentials, browser cookies or private keys.

| Tool / Skill / Plugin / MCP | Version / Source | Scope | Purpose | Access | Risk | Status |
|---|---|---|---|---|---|---|
| Claude Code | 2.1.263 | Local repo | Development agent | Project files + approved tools | Medium | Active |
| Codex CLI | 0.153.4 | Local repo (`~/.codex` global config) | Second development agent, parity with Claude Code | Project files + approved tools; repo `trust_level = "trusted"` | Medium | Active — reads `AGENTS.md`; setup via `tools/codex/setup-codex.ps1` |
| Node.js / npm | v24.19.0 / 11.17.0 | Local | Runs MCP servers (npx), Playwright sweep, hooks | Local | Low | Active — MCP servers must launch via `npx.cmd`/`cmd /c npx`, not bare `npx` (PowerShell policy) |
| Git | 2.55.0 | Local repo | Version control | Project files | Low | Active |
| Docker Desktop | 29.7.2 (Compose v5.5.0) | Local only | Runs the local WordPress stack | Local containers/volumes | Medium | Active |
| WordPress (local) | `wordpress:php8.3-apache`, WP 7.1 | Local only, `http://localhost` | Site under development | Local site/DB/files | Medium | Active — PHP 8.3 matches confirmed production |
| MariaDB (local) | `mariadb:11` | Local only | Local site database | Local container volume | Medium | Active |
| WP-CLI | `wordpress:cli-php8.3` (containerized) | Local only | Inspection, install, export, backups | Local WordPress/DB via `docker compose ... run --rm wpcli` or `local/wp.ps1` | Medium | Active |
| Elementor Free | 4.2.4 | Local site | Page builder | Local site | Low | Active |
| Hello Elementor | 3.5.1 | Local site | Parent theme | Local site | Low | Active |
| Easy Quran Classes child theme | 1.0.0, `wp-content/themes/easy-quran-classes-child/` | Local site (bind-mounted, tracked in Git) | Presentation code Elementor Free can't express | Local site | Low | Active |
| Playwright | 1.63.0, project-local (`tests/visual/`, not global) | Local browser | Scripted multi-viewport QA sweeps | Localhost only | Low | Active |
| `chrome-devtools` MCP | Already connected | Local/any browser | Interactive QA, Lighthouse audits | Browser | Low | Active |
| `context7` MCP | Already connected | Docs lookup | Current WordPress/Elementor API docs | Read-only web | Low | Active |
| `build-with-wordpress` plugin | Official (Anthropic) | Claude Code | WordPress/agent-skills content | Project files | Low | Enabled |
| `codex@openai-codex` | Installed | Claude Code | SVG ornament/asset subtasks via `/codex` | Local | Low | Enabled |
| `.mcp.json` (`novamira-localhost`) | `@automattic/mcp-wordpress-remote@latest` via npx | Local only | Claude Code ↔ Novamira MCP bridge | Local site via Application Password (`local/.env`, gitignored; referenced as `${NOVAMIRA_APP_PASSWORD}` from OS env) | Medium | Active |
| Codex MCP servers (`~/.codex/config.toml`) | `novamira-localhost` (via `tools/codex/novamira-mcp.cmd`), `chrome-devtools` (`cmd /c npx -y chrome-devtools-mcp@latest`), `context7` (`cmd /c npx -y @upstash/context7-mcp`) | Local only | Codex parity: WordPress control plane, browser/Lighthouse QA, library docs | Local site + browser + read-only web; Novamira password read from `local/.env` (never written to config) | Medium (Novamira: High) | Active — registered by `tools/codex/setup-codex.ps1`; verified end-to-end (36 abilities, context7 resolve) |
| `AGENTS.md` + `tools/codex/setup-codex.ps1` + `tools/codex/novamira-mcp.cmd` | Repo, tracked | Local | Codex onboarding: mandatory-reading pointer, idempotent MCP+skills setup with `-Verify`, credential-safe Novamira launcher | Project files | Low | Active |
| `.claude/hooks/guard-bash.mjs` | Repo, tracked | Claude Code | PreToolUse deny-guard (rm -rf roots, `DROP`/`TRUNCATE`, secret-file reads/exfil, AI-attribution trailers). Fails open. | Bash/PowerShell tool calls | Low | Active |
| `.claude/hooks/post-edit-validate.mjs` | Repo, tracked | Claude Code | PostToolUse advisory: `JSON.parse` on `.json`, `php -l` on `.php` (skipped if no host PHP) | Edited files | Low | Active |
| `.claude/settings.json` / `settings.local.json` | Repo (tracked) / gitignored | Claude Code | permissions allow/deny/ask, hooks, enabled plugins / per-machine MCP enablement | Project config | Low | Active — `settings.local.json` recreated per machine |
| `.claude/rules/*.md` | Repo, tracked (6 files) | Both agents | security, git, wordpress, implementation, design, deployment rules | Docs | Low | Active |
| `tools/*.php` build scripts | Repo, tracked | Local WP-CLI (`eval-file /tools/...`) | Idempotent site scaffolding (`00-site-setup`), Elementor kit (`01-elementor-kit`), demo posts (`02-demo-posts`), `elementor-helpers.php` library, `pages/*` builders (via `Document::save()`) | Local site | Low | Active |
| `tests/visual/sweep.mjs` | Repo, tracked; `playwright` 1.63.0 project-local | Local browser | 7-viewport full-page screenshot sweep; fails on HTTP ≥ 400 or horizontal overflow | Localhost only | Low | Active — `node tests/visual/sweep.mjs http://localhost/` |
| **Novamira** | v1.12.2 plugin + v1.1.0 CLI, `github.com/use-novamira/novamira`(-cli) (AGPL-3.0) | **Local site only** | Direct-connect WordPress MCP: PHP execution incl. `$wpdb`, WP-CLI, filesystem R/W | Full local site/DB/filesystem | **High** | **Active, connected via both MCP and CLI** — installed manually via WP Admin; MCP live via `.mcp.json`, verified with `mcp-adapter-discover-abilities`; CLI live as profile `eqc-local-file` (`novamira --site eqc-local-file doctor --json` → all checks pass except the expected `credential.backend: warn`), using `NOVAMIRA_CREDENTIAL_BACKEND=file` (persistent User env var) since the default Windows Credential Manager backend fails on this machine — see `CLAUDE.md` for the full diagnosis and the ACL fix required on `%LOCALAPPDATA%\Novamira\{Credentials,Cache}` |
| WPVibe (`vibe-ai`) | v1.16.4 | **Local site only, inactive** | Hosted cloud relay (`mcp.wpvibe.ai`); cannot route to `localhost` | Would be full local site/DB/filesystem if reachable | High | User installed it directly; deactivated (not deleted) since it has no working path to a local site — `release-check` asserts its absence from any release archive |
| novamira.ai marketing site | N/A | None | Returns HTTP 403; unrelated to the `use-novamira/novamira` GitHub project reviewed above | N/A | N/A | Not used |
| cPanel | Host UI | Production hosting | Manual deployment/admin | Account-wide UI | High | User-controlled |
| cPanel MCP | N/A | None | Not required | Would be broad | High | Do not install |
| SSH MCP / remote shell | N/A | None | Not available/needed (no shell on this hosting account) | N/A | High | Do not install |
| Generic DB MCP | N/A | None | Not required | Would expose DB | Critical | Do not install |
| `greptile` MCP | Configured | — | Code search | — | — | **Broken: HTTP 403 (`AUTH_HEADER_REJECTED`), unused — disable the plugin** |
| claude.ai WordPress.com connector | N/A | None | Targets WordPress.com-hosted sites only | N/A | N/A | Not applicable (self-hosted cPanel site) |

## Known Issues

Full symptom → cause → fix catalogue: **`README-SETUP.md` §7**. Historical log: **`TASK.md` §19**.
In brief:

- **Docker port** must be `80:80` (a `8080:80` mapping broke WP self-loopback / Novamira REST / WP-Cron).
- **`wpcli` service** pinned to `user: "33:33"` (Alpine vs Debian `www-data` uid mismatch).
- **`WP_ENVIRONMENT_TYPE=local`** required for App Passwords / OAuth over plain HTTP.
- **Git Bash**: prefix `/`-leading args with `MSYS_NO_PATHCONV=1`.
- **Novamira MCP (Claude Code)**: `NOVAMIRA_APP_PASSWORD` must be a Windows **User** env var; restart after setting.
- **Novamira CLI**: `NOVAMIRA_CREDENTIAL_BACKEND=file` + one-time `icacls` ACL repair on `%LOCALAPPDATA%\Novamira\{Credentials,Cache}`.
- **Codex**: Docker unreachable in-sandbox (approve out of sandbox); MCP servers run as `cmd /c npx …`; `*PASSWORD*` env vars are stripped (wrapper reads `local/.env`); MCP tools are deferred behind tool-search.
- **WPVibe** deactivated (cloud relay can't reach localhost); **greptile** disabled (403).

## Project Skills

- `wp-audit`
- `wp-cli-safe`
- `elementor-build`
- `visual-qa`
- `seo-review`
- `performance-audit`
- `cpanel-audit`
- `backup-verify`
- `release-check`
- `implementation-workflow`
- `git-checkpoint`
- `wordpress-debug`
- `plugin-evaluation`
- `wp-plugin-development` (bonus, reused as-is)

## Visual asset sources

- Pexels — photography of people (per `DESIGN.md` §10).
- Higgsfield/Canva MCP — abstract ornaments, patterns, backgrounds only; never AI-generated people.
- 21st.dev — inspiration only; its React/Tailwind output is not usable in Elementor.
