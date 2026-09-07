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

## Local Environment (established 2026-09-07)

Stack: **Docker Desktop** (Windows host, no WSL/LocalWP/XAMPP in play). Compose file: `local/docker-compose.yml`. Services: `wordpress` (`wordpress:php8.2-apache`, port `8080`), `db` (`mariadb:11`), one-shot `wpcli` (`wordpress:cli-php8.2`, **`user: "33:33"`** — required, see the compose file's comment: the CLI image is Alpine (`www-data`=82) while the Apache image is Debian (`www-data`=33); without matching uids, wp-cli can't write to uploads/plugins the Apache container owns).

- Site: `http://localhost:8080` · Admin: `http://localhost:8080/wp-admin/`.
- Run WP-CLI as `docker compose -f local/docker-compose.yml --env-file local/.env run --rm wpcli <args>`, or `local/wp.ps1 <args>` from PowerShell.
- **From Git Bash on Windows, prefix any command with a `/`-leading argument (permalink structures, WP-CLI export paths, route args) with `MSYS_NO_PATHCONV=1`** — otherwise MSYS silently rewrites it into a Windows path (e.g. `/backups/x.sql` → `C:/Program Files/Git/backups/x.sql`) and the command fails, or worse, silently no-ops against the wrong path.
- Named volume `eqc_wp` holds WordPress core/plugins/uploads (mutable local state, not in Git). Only `wp-content/themes/easy-quran-classes-child/` is bind-mounted from the repo. `local/backups/` is bind-mounted to `/backups` in the `wpcli` service for exports/checkpoints (gitignored).
- Visual QA: project-local Playwright (`tests/visual/`, not a global install — see `tests/visual/README.md`) for scripted multi-viewport sweeps against `DESIGN.md` §21 viewports; `chrome-devtools` MCP for interactive inspection and Lighthouse.
- **Novamira** (`github.com/use-novamira/novamira`, AGPL-3.0) is approved as a local-only WordPress MCP: direct connection (not a hosted relay, unlike WPVibe which was rejected), authenticated by Application Password, granting PHP execution/`$wpdb`/WP-CLI/filesystem access. Its own README says "For dev and staging environments. With backups. Always." — **never install on `easyquranclasses.com`**, always checkpoint the DB first, and `release-check` must assert it is absent from any release archive.

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
