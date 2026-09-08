/superpowers:using-superpowers

Use all RELEVANT existing skills, agents, connectors, browser tools and integrations intelligently to complete this deployment correctly.

Do not install or enable tools merely to satisfy this instruction.

Prefer existing project skills and approved tooling over adding new MCPs/plugins.


PROJECT:
Easy Quran Classes
WordPress + Elementor Free + Hello Elementor
Local-first development
Production hosting: cPanel

CONFIRMED HOSTING CONSTRAINT:
The client's cPanel DOES NOT provide Shell, SSH or Terminal access.

==================================================
PHASE 0 — READ AND REVIEW BEFORE EXECUTION
==================================================

Before doing ANYTHING, read COMPLETELY:

1. CLAUDE.md
2. DESIGN.md
3. CPANEL-WORKFLOW.md
4. TASK-DEPLOY.md
5. .gitignore
6. all .claude/rules/*.md
7. all relevant .claude/skills/*/SKILL.md
8. previous website build reports/checklists
9. relevant project documentation

Also inspect repository state and current local WordPress state.

Do not blindly execute TASK-DEPLOY.md.

FIRST perform a senior deployment review of TASK-DEPLOY.md.

Check it for:

- contradictions with CLAUDE.md
- contradictions with CPANEL-WORKFLOW.md
- assumptions requiring Shell/SSH
- unsafe database operations
- unsafe Git deployment assumptions
- unsafe File Manager operations
- missing rollback steps
- missing backup steps
- production path mistakes
- Git/Elementor database misunderstandings
- credentials/secrets exposure
- destructive FTP mirror/delete behavior
- poor WordPress migration practices
- performance/image optimization gaps

If TASK-DEPLOY.md contains an incorrect or outdated instruction:

1. explain the issue briefly;
2. determine the correct implementation;
3. update TASK-DEPLOY.md;
4. continue using the corrected task.

Do not execute a known-bad instruction merely because it exists in the source task.

==================================================
MANDATORY TODO / IMPLEMENTATION RULES
==================================================

Create a deployment TODO BEFORE implementation.

The TODO must include:

[ ] Scan repository and local WordPress state.
[ ] Identify reusable code/config/deployment logic.
[ ] State why new implementation is needed if reuse is unsuitable.
[ ] Choose the smallest maintainable solution.
[ ] Confirm normal + edge cases.
[ ] Confirm local vs production targets.
[ ] Verify backups and rollback before production changes.
[ ] Review Git-owned vs database-owned WordPress state.
[ ] Optimize production assets.
[ ] Review production package.
[ ] Test every code/configuration change.
[ ] Remove dead/debug/duplicate/unnecessary code within scope.
[ ] Update docs/checklists.
[ ] Update durable CLAUDE.md knowledge.
[ ] Keep CLAUDE.md under 1000 lines.
[ ] Review git status / diff.
[ ] Confirm no secrets/backups/DB dumps/uploads are tracked.
[ ] Verify production after every deployment stage.

GENERAL IMPLEMENTATION RULES:

Before implementing anything, scan the repository for existing reusable code/config.

Reuse existing implementation whenever it is clean and appropriate.

If reuse is not appropriate, state the reason briefly.

For every new feature/config:
- determine the minimal implementation;
- check whether an existing dependency already solves it;
- avoid duplicate libraries;
- avoid unnecessary abstraction.

Every code/configuration change must be tested, including realistic edge cases.

Do not create empty or unnecessary folders/files.

Every piece of logic must live in the appropriate file according to WordPress/Git best practices.

Remove dead, obsolete, duplicate or debug code encountered within the current implementation scope.

Do not leave broken experimental implementations behind.

After each meaningful iteration:
- update relevant docs/checklists;
- mark completed checklist items;
- update CLAUDE.md only with durable architecture/workflow knowledge.

Do NOT turn CLAUDE.md into a running log.

==================================================
TOOLS / SKILLS
==================================================

Use relevant existing skills such as:

- backup-verify
- release-check
- git-checkpoint
- performance-audit
- visual-qa
- wp-audit
- wp-cli-safe
- wordpress-debug
- plugin-evaluation
- implementation-workflow

Use Git/GitHub tooling when appropriate.

Use Claude in Chrome / browser automation when available for:

- cPanel inspection
- cPanel File Manager
- phpMyAdmin
- database management
- backup tools
- SSL checks
- GitHub repository/settings/workflows
- production browser QA

IMPORTANT:

The USER must enter:
- cPanel passwords
- GitHub passwords if requested
- 2FA codes
- DB secret values
- FTP/FTPS passwords

Never store these values in:
- Git
- CLAUDE.md
- TASK files
- screenshots intended for repo
- command history where avoidable
- logs
- generated documentation

Never expose secret VALUES in reports.

You may document secret NAMES only.

Do NOT install:

- cPanel MCP
- SSH MCP
- direct database MCP
- unrestricted filesystem MCP
- credential/session MCP

unless there is an independently justified requirement and explicit approval.

No such MCP should be required for this deployment.

==================================================
CPANEL BROWSER AUTOMATION SAFETY
==================================================

Browser automation is explicitly authorized for this deployment.

However, before any destructive production action:

1. confirm visible domain/account;
2. confirm exact document root/database;
3. confirm backup exists;
4. state intended action;
5. state potential impact;
6. state rollback;
7. request approval where required by project rules.

Examples requiring approval:

- deleting production files
- replacing an existing live site's files
- overwriting/importing an existing DB
- changing PHP versions/extensions
- changing SSL
- changing DNS/MX/email routing
- deleting database/users
- changing permissions broadly
- modifying repository protection
- destructive FTP mirror/delete behavior

If browser state is ambiguous:
STOP.

Do not guess which database/domain/directory is production.

==================================================
PHASE 1 — DEPLOYMENT PRE-FLIGHT
==================================================

Confirm and report:

LOCAL:
- project root
- local site URL
- WordPress version
- PHP version
- DB version
- active theme
- Elementor version
- plugins
- Git branch
- Git status
- Git remotes

PRODUCTION:
- domain
- canonical host: apex vs www (pin this BEFORE any search-replace; a wrong
  choice bakes the wrong host into every serialized row)
- cPanel document root
- production state: empty/new/existing
- PHP version
- MySQL/MariaDB
- existing databases already present in the account (identify each one
  before creating or importing anything — do not assume an empty document
  root means there are no existing databases)
- File Manager
- phpMyAdmin
- upload limit
- DB import limit
- backup/JetBackup availability
- SSL
- LiteSpeed/cache/CDN state
- FTP availability
- FTPS availability
- cPanel Git Version Control availability
- GitHub repo public/private status

Do not perform production writes during the initial audit.

==================================================
PHASE 2 — PRODUCTION BACKUP
==================================================

Before replacing any existing production data:

Create and verify:

1. filesystem backup
2. database backup

Record:
- timestamp
- location
- approximate size
- restore procedure

Do not continue until rollback is credible.

Never delete the last known-good backup.

==================================================
PHASE 3 — PRODUCTION OPTIMIZATION BEFORE RELEASE
==================================================

Perform a careful production optimization audit.

--------------------------------
IMAGE OPTIMIZATION
--------------------------------

Inspect all images actually used by the website.

For photographic content:

- resize oversized source images appropriately;
- convert suitable images to WebP;
- optionally consider AVIF only if it integrates cleanly;
- maintain visual quality;
- preserve correct aspect ratios.

Elementor stores attachment URLs inside serialized `_elementor_data`. Do
NOT replace Media Library files in place and call it done. Convert masters
in the local staging source, re-import as new attachments, then rebuild the
affected pages through the project's own Elementor Document API tooling
(`wp --user=1 eval-file tools/pages/NN-*.php`) so references re-bind
correctly. Never hand-edit `_elementor_data` to swap an image URL.

Do NOT blindly convert:

- SVG logos
- icons
- vector ornaments
- transparent graphics where PNG/SVG is superior
- raster images containing Quranic/Arabic text if conversion damages legibility

Keep appropriate original/master sources where necessary.

Use WordPress responsive images:
- srcset
- sizes
- generated thumbnails

Verify:
- no broken media references;
- no Elementor broken image references;
- mobile cropping;
- desktop cropping;
- visual quality.

Hero/LCP:
- appropriately sized;
- do not lazy-load likely LCP hero media.

Below fold:
- lazy-load where appropriate.

Do not destructively replace Media Library assets without backup.

--------------------------------
FRONTEND OPTIMIZATION
--------------------------------

Inspect:

- unused JS
- duplicate JS
- unused CSS
- duplicate CSS
- excessive Elementor addon assets
- unnecessary fonts
- unnecessary font weights
- large SVGs
- debug assets
- temporary animations
- excessive network requests

Keep implementation minimal.

Run performance/browser tests.

Focus on:
- LCP
- CLS
- responsive images
- font loading
- motion performance
- mobile load time

Do not destroy the visual design merely to chase a perfect Lighthouse score.

Do not install random optimization plugins.

Production caching must be based on the actual cPanel/server stack.

==================================================
PHASE 4 — CLEAN GIT / MAIN
==================================================

Deeply audit Git before deployment.

IMPORTANT — branch policy update (2026-09-07, explicit client decision,
supersedes this phase's original wording below):

`main` is production-deployment-only. It contains ONLY
`wp-content/themes/easy-quran-classes-child/` (and
`wp-content/plugins/easy-quran-classes-core/` if ever added) plus its own
`.gitignore`. No documentation, no `.claude/` configuration, no local
tooling, no reference assets, no tests — `main`'s `.gitignore` lists these
paths so they cannot be re-added by accident. All of that lives on
`feature/*` branches instead (currently `feature/setup`), which keep the
full development tree.

Do NOT `git merge` a feature branch into `main` — a full-branch merge
reintroduces every dev-only path `main` was deliberately pruned of. Release
by copying only the production paths across instead:

```
git checkout main
git checkout feature/setup -- wp-content/themes/easy-quran-classes-child
git commit -m "feat: <describe the change>"
```

(The original phrasing below — "Git may contain development/documentation
files... transfer only production-required paths [via the deploy workflow,
not Git itself]" — described an earlier, since-superseded approach. Do not
follow it; follow the branch policy above instead.)

Review:

git status
git diff --check
git diff

Inspect tracked files carefully.

Ensure there are no:

- DB dumps
- SQL exports
- backups
- deployment ZIPs
- wp-config.php
- .env
- passwords
- API keys
- FTP credentials
- browser auth state
- temporary files
- corrupted files
- abandoned experiments
- dead duplicate implementation
- huge unnecessary binaries
- AI-generated attribution text in commit messages

Do not blindly:

git add .

Stage intentionally.

Commit messages must be:

- clean
- clear
- concise
- natural
- normally one line

Examples:

chore: prepare production deployment
fix: optimize responsive images
fix: clean production assets
chore: add deploy workflow

Never use:

Generated by Claude
Generated with AI
Co-Authored-By: Claude
AI-assisted implementation

No force pushes.

==================================================
PHASE 5 — INITIAL WORDPRESS MIGRATION
==================================================

Remember:

The FIRST deployment is NOT Git-only.

WordPress/Elementor requires:

- files
- uploads
- database
- production configuration

--------------------------------
DATABASE URL MIGRATION
--------------------------------

Use local WP-CLI. This project has no bare `wp` binary — every command runs
through the Docker wrapper (see `wp-cli-safe` skill):

docker compose -f local/docker-compose.yml --env-file local/.env run --rm wpcli <args>

(or `local/wp.ps1 <args>` from PowerShell). From Git Bash, prefix any
`/`-leading argument (e.g. `/backups/...`) with `MSYS_NO_PATHCONV=1` or MSYS
will silently rewrite it into a Windows path.

Do not raw-search-replace SQL.

First:

... wpcli search-replace '<LOCAL_URL>' '<PRODUCTION_URL>' \
  --all-tables-with-prefix \
  --skip-columns=guid \
  --dry-run

Review results.

Then export:

... wpcli search-replace '<LOCAL_URL>' '<PRODUCTION_URL>' \
  --all-tables-with-prefix \
  --skip-columns=guid \
  --export=/backups/'<PRODUCTION_EXPORT>.sql'

Use REAL URLs only.

Keep SQL outside Git (`/backups` in the container is bind-mounted to
`local/backups/`, already gitignored).

Do not mutate the working local DB unnecessarily.

Before exporting: deactivate `novamira` and `vibe-ai` locally (both are
local-development-only AI-agent connectors that must never reach
production — see `release-check`), and add `--skip-tables=wp_novamira_*,wp_wpvibe_*`
to the export command rather than shipping those OAuth/audit tables to
production (verified empirically 2026-09-07: `--skip-tables` genuinely
excludes the table's data from the `--export` output, not just from the
replacement pass — confirmed via file-size delta on a real export, since
`.sql` content itself cannot be inspected directly). Reactivate `novamira`
locally afterwards.

**Do NOT delete the admin's Application Passwords directly** (e.g.
`wp user meta delete 1 _application_passwords`) as a way to keep them out of
production — that mutates the *live local working database* permanently
(confirmed the hard way 2026-09-07: it broke the local Novamira MCP
connection, which depends on one of those passwords, and had to be
regenerated). `--skip-tables`/table exclusion only touches the *export*, not
the working DB — use that pattern for any other local-only credential
consistently. Never `DROP`/`TRUNCATE` either way.

Note the site's `blog_public` setting travels with this export as-is. If it
is `0` (discourage search engines), the authoritative protection is
WordPress core's own `<meta name='robots' content='noindex, nofollow'>` tag —
confirmed present and correctly wired to `blog_public` on every page,
independent of any theme/plugin. **`robots.txt` itself is a separate, weaker
signal and can be misleading**: a WordPress-core refactor plus a Rank Math
conditional gate mean `do_robots()` no longer enforces `Disallow: /` on its
own when `blog_public=0` and no custom Rank Math robots.txt text is set — it
falls back to the permissive `Disallow: /wp-admin/` default (confirmed
present on both local and production; not a deployment artifact). This
doesn't cause indexing (the meta tag already blocks that) but does let
crawlers waste time fetching pages. Fix via Rank Math → General Settings →
Edit robots.txt, adding `Disallow: /` as custom content, if wanted. Confirm
the noindex meta tag (not just `blog_public`'s raw value or robots.txt) is
what you check when verifying a site is protected from indexing.

Elementor stores `_elementor_data`/`_elementor_element_cache` as JSON with
**escaped slashes** (`http:\/\/localhost`) that the search-replace above does
**not** catch (it only matches the literal unescaped form) — see
`CPANEL-WORKFLOW.md` §8 for the confirmed post-import verification/fix
required for this before QA can pass.

--------------------------------
FILES
--------------------------------

Determine production mode:

A. fresh WordPress already installed in cPanel
B. empty document root
C. existing live WordPress replacement

Do not blindly overwrite WordPress core.

If healthy WordPress core already exists and versions are compatible, prefer preserving it where practical and migrating the required application state.

If hosting is empty, choose the simplest reliable supported method.

Review release ZIP contents before upload.

Exclude:

.git/
unnecessary .github server files
local wp-config.php (and wp-config-docker.php)
.env
SQL dumps
backups
browser auth/test artifacts
cache
logs
temporary files
development junk
machine-specific files
wp-content/plugins/novamira/ — local-only AI-agent connector, full DB/
  filesystem access, must never reach production
wp-content/plugins/vibe-ai/ — local-only, deactivated, hosted relay with
  no working path on production either

Include the WordPress rewrite `.htaccess` — without it, permalinks
(`/%postname%/`) 404 on every page. Re-save Permalinks once after import as
a safety net regardless.

Preserve all website media actually required.

==================================================
PHASE 6 — CPANEL DEPLOYMENT
==================================================

Use browser automation carefully.

FILE MANAGER:

1. open exact document root
2. inspect existing contents
3. confirm backup
4. upload reviewed ZIP
5. verify filename + size
6. extract
7. inspect structure
8. confirm no accidental nested root
9. do not delete rollback backup yet

DATABASE:

1. create/select correct DB
2. create/select DB user
3. assign correct privileges
4. verify target
5. import transformed production SQL
6. inspect import result/errors

WP-CONFIG:

Create/configure production wp-config.php using production credentials
(user enters DB name/user/password directly in the cPanel File Manager
editor). Generate fresh unique authentication salts for production (never
reuse the local ones) via https://api.wordpress.org/secret-key/1.1/salt/.
Set `WP_DEBUG` false, `DISALLOW_FILE_EDIT` true, `WP_ENVIRONMENT_TYPE`
`'production'`.

Never commit it.

Never paste secret values into project docs.

==================================================
PHASE 7 — FIRST PRODUCTION QA
==================================================

Verify:

- homepage
- wp-admin
- HTTPS
- correct home/siteurl
- all 10 pages
- header/footer
- menus
- CSS
- JavaScript
- animations
- reduced-motion
- Elementor layouts
- fonts
- WebP images
- responsive images
- desktop layout
- tablet layout
- mobile layout
- Blog archive
- single posts
- categories
- Contact form
- Free Trial form
- outgoing email
- mixed content
- browser console
- network errors
- SEO canonical
- XML sitemap
- production indexing
- permalinks
- 404
- caching/CDN
- Lighthouse/performance

**Resolved 2026-09-08**: the previously-documented Rank Math per-page
`<title>`/meta-description gap (memory `elementor-document-api-gotchas`
point 17) turned out not to be a PHP/server-stack quirk at all — Rank
Math's setup wizard had simply never been completed on production
(confirmed by every Rank Math admin page returning "Sorry, you are not
allowed to access this page" and its menu link redirecting to
`admin.php?page=rank-math-registration`). Completing the wizard (Easy mode;
fixed two stale "(Local)" suffixes it surfaced in Website Name/Person-
Organization Name copied over from the local DB export) fixed titles, meta
descriptions, OG tags, AND the previously-404ing `sitemap_index.xml` in one
pass — all three symptoms shared this one root cause. **Canonical tag —
fully resolved 2026-09-08, confirmed intentional Rank Math behavior, not a
bug**: `<link rel="canonical">` is absent site-wide because Rank Math
deliberately omits canonical on any page it computes as `noindex`
(`Head::robots()`, citing a real SEO reference on noindex/canonical
conflict), and `blog_public=0` forces every page to `noindex` via
`Paper::respect_settings_for_robots()`. It will render automatically the
moment the client approves indexing (`blog_public` back to `1`) — no fix
required. Local's apparently-working canonical was **not** a valid
reference: confirmed via debug dump that `rank_math()->frontend`/`->head`
are never instantiated on local, so its canonical/robots tags come from
WordPress core's own native fallback, not Rank Math — see
`CPANEL-WORKFLOW.md` §19 for the full trace.

If needed:
- regenerate Elementor CSS/data (via wp-admin → Elementor → Tools →
  Regenerate CSS & Data — confirmed 2026-09-07 this alone does NOT fix
  stale JSON-escaped URLs baked into `_elementor_data` itself; that needs
  the separate database fix in `CPANEL-WORKFLOW.md` §8, this only
  regenerates the compiled CSS file cache);
- refresh permalinks;
- clear production cache/CDN (confirmed this account has an account-level
  NGINX reverse-proxy cache, separate from any WordPress cache — clear via
  cPanel home page's "NGINX Caching" widget → Clear Cache if a change
  doesn't appear to take effect).

After successful verification remove public:

- deployment ZIP
- SQL export
- temporary installers
- debug files

==================================================
PHASE 8 — GIT MAIN AUTO-DEPLOY
==================================================

main = production-ready CODE branch.

The desired workflow is:

feature/fix
    ↓
local tests
    ↓
review
    ↓
merge main
    ↓
push GitHub main
    ↓
GitHub Actions
    ↓
secure FTPS
    ↓
approved WordPress code paths
    ↓
production smoke test

IMPORTANT:

Because cPanel has NO SHELL access, do not assume cPanel-managed Git provides direct GitHub push auto-deploy.

Inspect the actual cPanel Git capability.

If only:
- Update from Remote
- Deploy HEAD Commit

are available, that is MANUAL pull deployment.

Do not call it automatic.

For TRUE automatic deployment, prefer:

GitHub Actions → secure FTPS

when supported by hosting.

Do not assume SFTP works merely because FTP exists.

==================================================
PHASE 9 — AUTO-DEPLOY SCOPE
==================================================

Automatically deploy ONLY Git-owned project code.

Likely:

wp-content/themes/easy-quran-classes-child/

and if used:

wp-content/plugins/easy-quran-classes-core/

Other paths require explicit review.

DO NOT synchronize all public_html.

DO NOT delete/overwrite through the Git workflow:

wp-content/uploads/
wp-config.php
WordPress core
production .htaccess unless intentionally Git-owned
unrelated plugins
server cache
backups
unrelated hosting files

Prefer explicit deployment paths over broad mirroring.

==================================================
PHASE 10 — GITHUB ACTIONS
==================================================

Before creating deployment workflow:

Inspect:
.github/workflows/

Reuse clean existing logic where appropriate.

If new workflow is required:

- smallest implementation;
- main-only production trigger;
- minimal permissions;
- no PR deployment;
- secrets only via GitHub Actions Secrets;
- deployment concurrency;
- clear failure;
- narrow transfer paths;
- reviewed third-party action;
- pinned stable version/commit where practical;
- optional workflow_dispatch.

Conceptual trigger:

on:
  push:
    branches:
      - main
  workflow_dispatch:

Do not blindly copy a generic FTP deployment YAML.

Configure against the ACTUAL cPanel FTP/FTPS account and target directories.

Secret values must never enter Git.

Only secret names may appear in documentation.

==================================================
PHASE 11 — MAIN BRANCH SAFETY
==================================================

If GitHub controls are available, evaluate:

- main branch protection
- PR requirement
- required checks
- force-push prevention
- deletion prevention

Do NOT change repository settings without approval.

==================================================
PHASE 12 — TEST AUTO-DEPLOY
==================================================

Perform one harmless real deployment test:

1. feature/fix branch
2. small identifiable code-only change
3. local test
4. edge-case test
5. git status
6. git diff --check
7. git diff
8. concise commit
9. merge main
10. push main
11. monitor Actions
12. verify production
13. confirm only intended paths changed
14. confirm DB untouched
15. confirm uploads untouched
16. confirm wp-config untouched
17. smoke-test affected page
18. remove/revert diagnostic test if required

Do not call auto-deploy successful until this real test passes.

==================================================
IMPORTANT WORDPRESS DATABASE RULE
==================================================

After launch:

Git main auto-deploys CODE.

It does NOT deploy:

- Elementor page edits
- WordPress Posts
- WordPress Pages
- menus stored in DB
- plugin settings
- SEO metadata stored in DB
- media uploads

Normal editorial changes may be performed in production wp-admin where appropriate.

Large Elementor changes developed locally require their own controlled migration/backup process.

NEVER replace the entire production DB merely to deploy a CSS/PHP change.

NEVER auto-sync the production DB from Git.

==================================================
ROLLBACK
==================================================

CODE DEPLOYMENT FAILURE:

1. identify last known-good commit
2. create a normal revert commit
3. push revert to main
4. allow GitHub Actions to redeploy
5. smoke-test

Do not rewrite Git history.

INITIAL MIGRATION FAILURE:

1. restore filesystem backup
2. restore DB backup
3. restore production configuration
4. clear cache
5. verify old site

==================================================
FINAL CLEANUP
==================================================

After everything succeeds:

- remove public ZIP/SQL installers
- verify no debug mode/output
- verify no unused deployment temp files
- verify no secret files entered Git
- verify optimized media
- verify cache/CDN
- verify production Lighthouse
- verify forms/email
- verify backups remain available
- update deployment docs
- update CLAUDE.md durable deployment knowledge
- mark source checklists complete

==================================================
DEFINITION OF DONE
==================================================

Do not mark complete until:

[x] TASK-DEPLOY.md reviewed/corrected
[x] production state audited
[x] backups verified
[x] local release QA passed
[x] images optimized/WebP where appropriate
[x] media quality verified
[x] frontend assets optimized
[x] production SQL exported safely
[x] production package reviewed
[x] first cPanel files deployment succeeded
[x] DB import succeeded
[x] wp-config configured safely
[x] all 10 pages work (verified across two QA passes; homepage/About/Contact
    freshly re-checked 2026-09-08 after the Rank Math fix, remaining pages
    verified in the initial post-migration QA pass)
[x] Elementor works
[x] Blog works
[x] forms work (Contact + Free Trial verified via real stored submissions)
[ ] email works — confirmed BROKEN 2026-09-08: a real Contact-form test
    submission stored correctly but no notification email arrived at
    `admin_email` (inbox or spam) — default host PHP `mail()` does not
    deliver here. Fix (activating "Email Deliverability" + connecting an
    Elementor account) intentionally deferred by client decision until the
    real client email address is ready — see `CPANEL-WORKFLOW.md` §17.
    Do not mark this done until re-tested after that connection is made.
[x] responsive QA works (desktop + mobile emulation verified on production)
[x] HTTPS/mixed-content clean
[x] SEO URLs/indexing correct (sitemap + per-page titles/meta fixed
    2026-09-08 — see `CPANEL-WORKFLOW.md` §19; canonical tag absence is
    confirmed intentional Rank Math behavior tied to noindex, not a defect
    — resolves itself automatically once indexing is approved)
[x] production performance checked
[x] main contains clean production-ready project code
[x] no secrets/DB/backups/uploads tracked
[x] no-shell auto-deploy mechanism selected (GitHub Actions → FTPS —
    cPanel's own Git Version Control confirmed to require shell access this
    account doesn't have)
[x] GitHub Actions configured
[x] auto-deploy paths narrow/reviewed (FTP account physically scoped to
    `wp-content/themes/easy-quran-classes-child/` only)
[x] real main auto-deploy test passed (2026-09-08: pushed a version-bump
    commit to `main`, watched it land on production automatically within
    the pipeline's normal run time, confirmed via the live file content and
    the enqueued `?ver=` query string after clearing the NGINX cache)
[x] DB/uploads/wp-config untouched by Git deploy (physically impossible —
    the deploy FTP account's home directory is the theme folder itself)
[x] production smoke test passed
[x] rollback documented and proven (reverted the test commit through the
    same pipeline and confirmed production rolled back automatically)
[x] public deployment temp files removed
[x] docs/checklists updated
[x] CLAUDE.md updated and under 1000 lines (317 lines)

==================================================
FINAL REPORT
==================================================

Report:

1. Deployment Summary
2. Tools / Skills Actually Used
3. Production Environment
4. Backups / Rollback
5. Optimization Performed
6. Initial Migration
7. Production QA
8. Git Main Workflow
9. GitHub Actions Configuration
10. Auto-Deployed Paths
11. Explicitly Excluded Paths
12. Elementor / Database Deployment Boundary
13. Performance Results
14. Git Commits / Status
15. Remaining Risks / Manual Steps

Never include secret values.

Only finish with:

PRODUCTION DEPLOYED — MAIN AUTO-DEPLOYS APPROVED CODE CHANGES

if BOTH:

1. the production website deployment succeeded; AND
2. a real main → production automatic deployment test succeeded.

Otherwise state the exact incomplete phase and continue/fix it rather than claiming success.