# Easy Quran Classes — Claude Code Instructions

## Project
Easy Quran Classes is an English WordPress marketing/enrollment website for online Quran education.

Target stack:
- WordPress
- Elementor Free
- Hello Elementor parent theme
- small Easy Quran Classes child theme for presentation code when needed
- site-specific plugin only if business functionality genuinely requires one
- native WordPress Posts for the blog

Client screenshots are the primary visual reference. Riwaq Al Quran is UX/content inspiration only and must not be cloned. Before frontend or Elementor work, read `DESIGN.md`.

## Known Hosting Constraint
The client's cPanel account **does not provide Shell/SSH access**.

Therefore the approved architecture is **local-first development**:

`Claude Code + local WordPress + local WP-CLI + browser QA -> verified release package -> manual cPanel deployment`

Do not waste time probing SSH, remote WP-CLI, rsync, cPanel Terminal, or remote shell workflows unless the user later confirms the hosting plan changed.

cPanel is the final hosting/deployment target, not the development environment.

## Local Environment (established 2026-09-07, last updated 2026-09-07)

Stack: **Docker Desktop**. Compose file: `local/docker-compose.yml`. Services: `wordpress` (`wordpress:php8.3-apache`, port `80` — **matches confirmed production PHP 8.3** from cPanel MultiPHP Manager), `db` (`mariadb:11`), one-shot `wpcli` (`wordpress:cli-php8.3`, **`user: "33:33"`** — required: the CLI image is Alpine (`www-data`=82) while the Apache image is Debian (`www-data`=33); without matching uids, wp-cli can't write to uploads/plugins the Apache container owns).

- Site: `http://localhost` · Admin: `http://localhost/wp-admin/`. **Host port must equal Apache's internal port (80).** A mismatched mapping (the original `8080:80`) breaks any WordPress self-loopback request — "localhost:8080" resolves to nothing *inside* the container, only Docker's host-side proxy understands that mapping. This broke Novamira's own REST self-check with `cURL error 7: Failed to connect to localhost port 8080`.
- `WORDPRESS_CONFIG_EXTRA` sets `WP_ENVIRONMENT_TYPE=local` — required by WordPress core itself (not Novamira-specific): Application Passwords and OAuth both refuse plain HTTP unless the environment is explicitly declared local.
- Run WP-CLI as `docker compose -f local/docker-compose.yml --env-file local/.env run --rm wpcli <args>`, or `local/wp.ps1 <args>` from PowerShell.
- **From Git Bash on Windows, prefix any command with a `/`-leading argument with `MSYS_NO_PATHCONV=1`** — otherwise MSYS silently rewrites it into a Windows path (e.g. `/backups/x.sql` → `C:/Program Files/Git/backups/x.sql`) and the command fails or silently targets the wrong path.
- Named volume `eqc_wp` holds WordPress core/plugins/uploads (mutable local state, not in Git). Only `wp-content/themes/easy-quran-classes-child/` is bind-mounted from the repo. `local/backups/` is bind-mounted to `/backups` in the `wpcli` service (gitignored).
- Visual QA: project-local Playwright (`tests/visual/`, not a global install) for scripted multi-viewport sweeps; `chrome-devtools` MCP for interactive inspection and Lighthouse.
- **Novamira** (`github.com/use-novamira/novamira`, AGPL-3.0) is installed, active, and connected via **two independent, both-working paths**. Local-only — never on production; DB checkpoint taken before install (`local/backups/pre-novamira-checkpoint.sql`); `release-check` asserts its absence from any release archive.
  - **MCP** (`.mcp.json` at repo root → `novamira-localhost` server, using `@automattic/mcp-wordpress-remote`; verified via `mcp-adapter-discover-abilities`). The real Application Password lives only in `local/.env` (`NOVAMIRA_APP_PASSWORD`); `.mcp.json` references it as `${NOVAMIRA_APP_PASSWORD}`, which must also exist as a real Windows **User** environment variable for Claude Code's substitution to resolve it — see `local/README.md`.
  - **CLI** (`novamira --site eqc-local-file <command>`, `@novamira/cli` v1.1.0). Its default Windows Credential Manager backend fails on this machine — confirmed *not* sandbox-specific (same `Error: The OS credential service could not complete the operation` in a normal interactive terminal) — root cause never fully isolated (the CLI's own `Add-Type`/`CredWrite` P/Invoke sequence succeeds when run directly, but fails inside the CLI's actual child-process invocation). Fixed by forcing the CLI's documented file-based fallback: `NOVAMIRA_CREDENTIAL_BACKEND=file`, set as a persistent Windows **User** env var. That fallback's own safety check requires each of `%LOCALAPPDATA%\Novamira\Credentials` and `...\Novamira\Cache` to carry **exactly one** ACL entry (the current user, full control, inheritance disabled) — a shared `CodexSandboxUsers` group's inherited Read+Execute permission on this machine violated that; fixed with `icacls <dir> /inheritance:r /grant:r "<user>:(OI)(CI)F"` (no additional principals — adding `SYSTEM` still fails the "exactly one rule" check). `novamira doctor --json` now reports all checks passing except the expected `credential.backend: warn` (file fallback isn't OS-encrypted — an accepted local-only tradeoff).
  - Both Bash/PowerShell tool calls referencing "novamira" and the plugin's own `wp plugin install ... --activate` install command were intermittently denied by the Claude Code auto-mode safety classifier during setup, independent of `.claude/settings.json` allow rules — retrying (sometimes with a different but equivalent command) got past it each time; MCP tool calls are a different mechanism and were never affected.
  - Repeated failed device-flow login attempts each register a new OAuth client server-side regardless of local outcome, capped by a WordPress transient (`_transient_novamira_oauth_dcr_0_<hash>`, cap 10) → `Error [rest_error]: Too many registrations`. Fix: delete that transient pair (`wp option delete _transient_novamira_oauth_dcr_0_<hash> _transient_timeout_novamira_oauth_dcr_0_<hash>`) or wait out its expiry. Stale rows in `wp_novamira_oauth_clients`/`_access_tokens`/`_auth_codes`/`_device_codes`/`_pending_authorizations` are safe to clear the same way (plugin housekeeping tables, not Elementor/serialized data) but are not themselves the limiter.
- **WPVibe** (`vibe-ai` plugin) was installed by the user directly, then **deactivated** — it's a hosted cloud relay (`mcp.wpvibe.ai`) that cannot reach `localhost` and has no working path here without publicly tunnelling the dev machine. Left installed-but-inactive rather than deleted; `release-check` also asserts its absence from any release archive.
- Production confirmed: `https://easyquranclasses.com` is empty (Mode A, no existing content to preserve) — see `CPANEL-WORKFLOW.md`.
- **First-run bootstrap (added 2026-09-08):** `local/bootstrap.ps1` / `.sh` rebuild an entire local site from a fresh clone in one command — `wp core install`, plugins, parent theme, `local/media-staging/` import, then `tools/00-04*.php` and every `tools/pages/*.php`, via `tools/05-bootstrap.php`. Page builders resolve their target post by slug (`eqc_page_id()` in `tools/elementor-helpers.php`, mirrors `eqc_media_id()`) rather than a hardcoded ID, so the same scripts work on any machine's install order. See `README-SETUP.md` §3 / §3A for the full procedure and the multi-machine/teammate workflow.
- **Permalinks are part of the bootstrap now (fixed 2026-09-08):** `00-site-setup.php` sets `permalink_structure` to `/%postname%/` and flushes rewrite rules — `wp core install` never sets this, and without it every `/slug/` URL on a fresh install silently falls back to serving the front page's content instead of 404ing or erroring, which is easy to mistake for a broken build. Found via an isolated fresh-clone-and-Docker verification (separate project name/volumes, cleaned up after) run specifically to diagnose why a teammate's machine wasn't showing the same site as this one — see `README-SETUP.md` §7's "Every page shows the same homepage content" row.

### Codex parity (added 2026-09-07)

The **Codex CLI** works this repo with the same capabilities as Claude Code. Full teammate
setup + the known-issues catalogue: `README-SETUP.md` (§5 Codex, §7 issues).

- **`AGENTS.md`** (repo root) — thin: points Codex at `CLAUDE.md` + `.claude/rules/*.md` +
  `DESIGN.md` + `CPANEL-WORKFLOW.md` as mandatory reading, plus Codex-specific operating notes.
  `CLAUDE.md` stays the single source of truth.
- **`tools/codex/setup-codex.ps1`** (idempotent, `-Verify`) — registers three MCP servers
  **globally** in `~/.codex/config.toml` (`novamira-localhost`, `chrome-devtools`, `context7`),
  syncs the 14 `.claude/skills/` as `~/.codex/skills/eqc-*` (junction; copy fallback), trusts the repo.
- **`tools/codex/novamira-mcp.cmd`** — Novamira launcher. Reads `NOVAMIRA_APP_PASSWORD` from
  `local/.env` (Codex strips `*PASSWORD*` env vars before spawning MCP servers, and has no
  `${VAR}` substitution in config) — the secret never touches `~/.codex/config.toml` or Git.
- Codex gotchas (all handled by the script/wrapper, documented in `AGENTS.md`): Docker is
  unreachable inside both Codex sandbox modes → Docker/WP-CLI commands must be approved out of
  the sandbox; `npx.ps1` is execution-policy blocked → MCP servers run as `cmd /c npx …`;
  Codex can't spawn `.cmd` directly with working stdio → the `cmd /c` wrapper; MCP tools are
  deferred behind tool-search → the model must search, not list.

## Core Method
Inspect first. Reuse second. Change third. Verify fourth.

Prefer the simplest native WordPress/Elementor solution that satisfies the requirement. Do not over-engineer or refactor unrelated code. Never guess project state, WordPress state, plugin/theme state, database state, local environment, or cPanel capabilities when they can be safely inspected.

## Local Development Ownership
Claude may work against the approved **local WordPress installation** using:
- local filesystem/project files
- local WP-CLI
- local PHP/Composer/Node tooling where justified
- browser automation against the local WordPress site
- WordPress Admin / Elementor on localhost
- local database commands only through the approved development workflow

Before local WordPress writes, identify the local target and confirm it is not production.

Never point local automation at the production domain by accident.

## cPanel / Production Boundary
Because no shell access exists, Claude must not assume it can remotely modify production through terminal commands.

Production deployment is a separate, approval-gated milestone using cPanel UI capabilities such as:
- File Manager upload/extract
- phpMyAdmin import
- MySQL/Manage My Databases
- MultiPHP Manager / PHP selector if needed
- SSL/TLS Status
- Backup/JetBackup if available

By default, the user performs cPanel login and sensitive UI actions. Claude prepares exact deployment packages, checklists, transformed database exports, and step-by-step instructions.

Do not automate cPanel login, capture browser credentials, or use a third-party cPanel MCP unless the user explicitly asks and the security model is reviewed first.

## Production Gate
Do not prepare a release as "ready to upload" until:
- local site QA is complete;
- local backup/snapshot exists;
- release files are reviewed;
- a production database export is generated safely;
- the production domain is confirmed;
- existing production filesystem/database backups are confirmed;
- rollback is documented;
- user explicitly approves deployment.

Do not overwrite an existing production site unless the deployment plan explicitly says to do so.

## Stop and Ask
Request approval before:
- production file replacement/import
- production database import or replacement
- deleting production files/content/plugins/themes/uploads
- DNS, SSL/TLS, email-routing or cron changes
- broad production permission changes
- changing PHP version/extensions on production
- force push or overwriting backups
- installing a migration/security/cache plugin on production

Show the exact intended action, impact, and rollback first.

## Never Do
Never:
- modify WordPress core as a customization
- edit Hello Elementor parent theme
- permanently patch third-party plugin source
- directly rewrite Elementor `_elementor_data` during normal development
- use raw SQL search/replace on Elementor/serialized WordPress data
- drop/truncate/reset production database tables
- hardcode/log credentials
- commit `wp-config.php`, `.env`, keys, DB dumps or backups
- install nulled software or execute downloaded scripts blindly
- disable SSL/security controls merely to make something work
- create unnecessary files/folders or parallel implementations

## Architecture
Expected custom-code locations:
- `wp-content/themes/easy-quran-classes-child/`
- `wp-content/plugins/easy-quran-classes-core/` only if required

Theme owns presentation. A site plugin owns business functionality that should survive a theme change. Elementor owns page content/composition, containers, responsive layout and supported global styles.

The full local WordPress site/database is **not** represented by Git alone. Elementor data, WordPress settings, posts, media and plugin settings require local backups/export packages.

## Responsive WordPress Stack — Mandatory
Use **one cohesive WordPress presentation stack**. Do not mix multiple parent themes, CSS frameworks, page builders or Elementor addon ecosystems to solve responsiveness.

Approved default stack:
- WordPress core for CMS, menus, media and dynamic content;
- Hello Elementor as the lightweight parent theme;
- Easy Quran Classes child theme for shared presentation code/templates only where needed;
- Elementor Free Containers/Flexbox for page composition;
- native WordPress Posts/query behavior for the blog;
- minimal project-owned CSS/JS/SVG for responsive behavior and motion Elementor Free cannot express cleanly;
- one lightweight plugin per genuinely missing responsibility when approved.

Do not switch themes mid-build merely because another theme has a convenient widget. Re-evaluate the parent theme only **before substantial page implementation** if a measurable accessibility, responsive-layout or maintainability blocker exists, and explain migration cost before changing the approved stack.

Responsiveness is **fluid, not breakpoint-only**. Build mobile-first and make layouts interpolate cleanly between phones, tablets, laptops, desktops and wide screens. Prefer CSS Grid/Flexbox, `minmax()`, `clamp()`, percentages, `max-width`, `aspect-ratio`, wrapping and responsive WordPress images (`srcset`/`sizes`) over fixed pixel canvases.

Avoid fixed section heights and fixed card/text widths unless the design genuinely requires a bounded control. No page may depend on one exact viewport size to look correct.

Dynamic WordPress behavior should come from WordPress where it adds maintainability: menus, Posts, archives, categories, shared templates and reusable global components. Do not hardcode dynamic lists into ten separate pages. Do not create custom post types or frameworks merely to make the site feel "dynamic"; introduce them only when they materially improve future administration.

## Elementor
Use Elementor Free containers/flexbox. Prefer global colors/typography, reusable classes, native widgets and child-theme CSS for reusable styling Elementor Free cannot express cleanly.

Avoid spacer-based layouts, arbitrary margins, excessive negative margins, addon-pack bloat and direct Elementor database manipulation.

For repetitive Elementor/admin work, browser automation against the local site is allowed when reliable. Always visually verify the result in the browser.

## WordPress Code
For custom PHP where relevant:
- sanitize input and validate trust boundaries
- escape output
- use nonces for state-changing requests
- check capabilities
- use prepared queries if direct SQL is truly necessary
- use hooks/actions/filters appropriately
- enqueue assets correctly
- avoid hardcoded site URLs

Do not build abstractions for hypothetical future requirements.

## Content Integrity
Do not invent student/review counts, testimonials, teacher identities, qualifications, certifications, experience, ratings, prices, guarantees, accreditation or business statistics. Use clearly marked local placeholders when verified information is unavailable. Never knowingly publish placeholders as facts.

## SEO / Accessibility / Performance
Maintain one appropriate H1, logical H2/H3 hierarchy, clean permalinks, useful internal links, valid canonicals/meta and factual schema only. Do not run competing SEO plugins.

Preserve semantic HTML, keyboard navigation, focus states, labels, contrast, alt text and reduced-motion behavior where custom motion exists.

Before adding performance plugins, inspect hosting cache/CDN and local frontend weight. Prefer properly sized AVIF/WebP, limited font weights and minimal JS.

## Git / GitHub
Git tracks custom code, Claude configuration, docs and reproducible local tooling — not the mutable WordPress database/media state.

**Branch policy (confirmed 2026-09-07):** `main` is production-deployment-only —
it holds nothing but `wp-content/themes/easy-quran-classes-child/` (and
`wp-content/plugins/easy-quran-classes-core/` if ever added), its own
`.gitignore` (which lists every dev-only path so they can't be re-added by
accident), and `.github/workflows/` (the auto-deploy pipeline itself — it
must live on `main` for GitHub Actions to trigger on push to `main`). All
development, docs, `.claude/` configuration, local tooling, reference
assets and tests live on `feature/*` branches (currently `feature/setup`).
Never `git merge` a feature branch into `main` — that reintroduces the
dev-only tree. Release by copying only the production paths across (`git
checkout feature/setup -- wp-content/themes/easy-quran-classes-child` on
`main`, then commit) — see `.claude/rules/git.md` for the exact flow.
**When multiple sessions may share this working directory, do the actual
`main` commit/push work in a separate `git worktree` (`git worktree add
../<name> main`) instead of `git checkout main` here** — switching the
shared checkout's branch would yank the floor out from under any other
session with uncommitted `feature/setup` work in progress.

Before commits:
- inspect `git status`
- run `git diff --check`
- review relevant diff
- verify no secret/generated/release files were added
- run applicable checks
- stage only intended files

Commit messages must be clean, clear, concise and brief. Prefer one line such as `feat: add homepage course grid` or `fix: correct mobile header spacing`.

Do not use long AI-style commit messages. Do not add `Generated by Claude`, `Generated with AI`, `Co-Authored-By: Claude`, or similar AI attribution.

Do not push unless explicitly authorized. Do not force-push, rewrite shared history, merge PRs, alter GitHub repository settings, or change branch protection without approval.

## WP-CLI
WP-CLI is a **local development tool** for this project unless hosting capabilities later change.

Use it for local inspection, plugin/theme management, cache operations, database exports and safe URL transformation.

For production release export, prefer serialization-safe WP-CLI search/replace export instead of raw SQL replacement, for example after the production URL is confirmed:

`wp search-replace '<local-url>' '<production-url>' --all-tables-with-prefix --skip-columns=guid --export='<release-file>.sql'`

Run a dry-run first where applicable. The export form must not mutate the working local database.

`tools/pages/*.php` (run via `wp --user=1 eval-file /tools/pages/<name>.php`)
are the only sanctioned way to change a page's `_elementor_data` — they
call helpers in `tools/elementor-helpers.php`, some of which (e.g.
`eqc_divider_svg()`/`eqc_get_svg_asset()`, `inc/template-tags.php`) embed
a *snapshot* of a generated SVG's current file content as a literal HTML
string. Regenerating that SVG afterward does not change already-saved
pages — only a fresh `eval-file` run does (see
`tools/graphics/README.md`'s "some assets need the Elementor pages
re-baked too" for the full mechanism and how this was diagnosed).
**Always pass `--user=1`** — omitting it still prints `Success: Saved
Elementor content for post #N`, but the save silently doesn't take;
confirmed once by re-querying `_elementor_data` and finding old content
still there after an apparently-successful run without it.

## Deployment Model
Default release process:
1. complete local WordPress/Elementor build;
2. local functional/responsive/SEO/performance QA;
3. create local filesystem + database backup;
4. identify exact production URL and cPanel document root;
5. prepare release archive(s);
6. generate production-URL database SQL export safely from local WP-CLI;
7. back up existing production files/database through cPanel;
8. upload/extract files through cPanel File Manager;
9. create/select DB and import SQL through phpMyAdmin;
10. configure production `wp-config.php` manually without committing secrets;
11. verify HTTPS/site URLs/permalinks/forms;
12. regenerate Elementor CSS/data and clear caches;
13. run production smoke test;
14. remove temporary installers/archives from public web root.

The exact deployment variant depends on whether production is empty, a new WordPress install, or an existing live site. Read `CPANEL-WORKFLOW.md` before any release.

**Confirmed gotchas from the first production migration (2026-09-07)**, full
detail in `CPANEL-WORKFLOW.md` §8: (1) the standard `wp search-replace` URL
migration misses Elementor's JSON-escaped URLs (`http:\/\/old` vs.
`http://old`) — invisible locally, surfaces as mixed-content warnings on
specific images/widgets after a real domain migration, needs a documented
manual phpMyAdmin fix post-import; (2) `blog_public=0`'s authoritative
enforcement is the `noindex` meta tag (always correctly wired), not
`robots.txt` (a WP-core-refactor + Rank Math interaction leaves it
permissive by default — cosmetic, not an indexing risk, but worth knowing);
(3) never delete local credentials (e.g. an Application Password) directly
to keep them out of a release export — that mutates the live local DB;
exclude them from the export instead (`--skip-tables`).

## Verification
A file change is not completion. Verify with the relevant combination of:
- local browser
- WordPress Admin
- Elementor
- local WP-CLI
- PHP lint
- browser console/network
- Playwright
- Lighthouse
- responsive screenshots

If verification was not possible, say so.

## Implementation Workflow
Every implementation task begins with a TODO list. Before editing, scan the repository/relevant WordPress state, identify reusable code/features/libraries, and reuse them when suitable. If a new implementation is necessary, state why.

Prefer the smallest maintainable implementation, use native WordPress/Elementor capabilities before adding dependencies, place logic in the correct existing file, test modified code including relevant edge cases, remove temporary/dead code made obsolete by the change, and update affected docs/checklists. Do not create empty or unnecessary files/folders.

Keep `CLAUDE.md` current with durable architecture/workflow knowledge after meaningful iterations. Do not use it as a task log and never allow it to exceed 1000 lines.

## Task Workflow
1. Create the mandatory TODO/reuse/test checklist.
2. State goal and inspect current state.
3. Identify reusable implementation and files/data affected.
4. State risk level and smallest correct approach.
5. Implement only the focused local change.
6. Remove obsolete temporary/dead code within scope.
7. Review diff/state.
8. Test normal and relevant edge cases.
9. Update docs/checklists and durable `CLAUDE.md` knowledge.
10. Report manual checks still required and suggest a concise Git commit message when appropriate.

## Final Report
Always report:
- summary
- environment affected (`local` or `production-package`; never imply remote shell access)
- files/data changed
- commands executed
- verification performed
- manual verification remaining
- risks/open questions
- suggested Git commit message
