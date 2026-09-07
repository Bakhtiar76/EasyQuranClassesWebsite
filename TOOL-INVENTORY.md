# Easy Quran Classes — Tool Inventory

Update this after any local setup change. Do not record passwords, tokens, DB credentials, browser cookies or private keys.

| Tool / Skill / Plugin / MCP | Version / Source | Scope | Purpose | Access | Risk | Status |
|---|---|---|---|---|---|---|
| Claude Code | 2.1.263 | Local repo | Development agent | Project files + approved tools | Medium | Active |
| Git | 2.55.0 | Local repo | Version control | Project files | Low | Active |
| Docker Desktop | 29.7.2 (Compose v5.5.0) | Local only | Runs the local WordPress stack | Local containers/volumes | Medium | Active |
| WordPress (local) | `wordpress:php8.3-apache`, WP 7.1 | Local only, `http://localhost:8080` | Site under development | Local site/DB/files | Medium | Active — PHP 8.3 matches confirmed production |
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
| **Novamira** | v1.12.2, `github.com/use-novamira/novamira` (AGPL-3.0) | **Local site only** | Direct-connect WordPress MCP: PHP execution incl. `$wpdb`, WP-CLI, filesystem R/W | Full local site/DB/filesystem | **High** | Approved local-only; user-approved, but **install blocked by the Claude Code auto-mode classifier** even with an explicit scoped allow rule — needs manual install via WP Admin → Plugins → Add New → Upload, or a session/settings change outside this session's control |
| WPVibe | N/A | None | Rejected — hosted cloud relay (`mcp.wpvibe.ai`), cannot route to localhost, stores credentials off-machine | N/A | High | Do not install |
| novamira.ai marketing site | N/A | None | Returns HTTP 403; unrelated to the `use-novamira/novamira` GitHub project reviewed above | N/A | N/A | Not used |
| cPanel | Host UI | Production hosting | Manual deployment/admin | Account-wide UI | High | User-controlled |
| cPanel MCP | N/A | None | Not required | Would be broad | High | Do not install |
| SSH MCP / remote shell | N/A | None | Not available/needed (no shell on this hosting account) | N/A | High | Do not install |
| Generic DB MCP | N/A | None | Not required | Would expose DB | Critical | Do not install |
| `greptile` MCP | Configured | — | Code search | — | — | Broken: HTTP 403 (`AUTH_HEADER_REJECTED`), unused by this project |
| claude.ai WordPress.com connector | N/A | None | Targets WordPress.com-hosted sites only | N/A | N/A | Not applicable (self-hosted cPanel site) |

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
