# Easy Quran Classes — Local Development to cPanel Deployment Workflow

## 1. Confirmed Constraint

The client's cPanel account **does not have Shell/SSH/Terminal access enabled**.

This changes the project workflow permanently unless hosting access changes later.

Claude Code will develop and test the website **locally**. cPanel is used only for hosting, backup, database management and final deployment through the web UI.

Do not plan around:
- remote SSH
- remote WP-CLI
- rsync
- SCP
- cPanel Terminal
- remote shell deployment scripts
- server-side Git commands that require shell access

## 2. Approved Architecture

```text
Local machine
  ├─ Claude Code
  ├─ Git
  ├─ Local WordPress
  ├─ Elementor Free
  ├─ Local WP-CLI
  ├─ Playwright / browser QA
  └─ Local database + media
          │
          ▼
Verified release package
  ├─ site files/archive
  └─ serialization-safe production SQL export
          │
          ▼
Manual cPanel deployment
  ├─ File Manager
  ├─ Manage My Databases / MySQL Databases
  ├─ phpMyAdmin
  ├─ MultiPHP Manager if needed
  ├─ SSL/TLS Status
  └─ Backup / JetBackup if available
          │
          ▼
Production smoke test + rollback readiness
```

## 3. Local Environment Preference

Choose the local environment after auditing the machine.

Preferred order:
1. an existing healthy local WordPress environment already used for the project;
2. Docker Desktop + Docker Compose when available and reliable, because Claude can reproduce services and use WP-CLI predictably;
3. LocalWP when Docker is unavailable or a GUI-managed environment is preferable;
4. XAMPP/WAMP only as a fallback when the above options are impractical.

Do not install multiple local stacks for the same project without a concrete reason.

Match the production PHP major/minor version as closely as practical once cPanel's PHP version is known.

## 4. Local Development Rules

Development happens only on the local site until release approval.

Claude may:
- install/activate approved WordPress plugins locally;
- configure Elementor locally;
- create pages/posts locally;
- use local WP-CLI;
- automate local WordPress Admin with Playwright when reliable;
- inspect local DB state through WordPress/WP-CLI;
- create local backups and release exports.

Claude must not:
- point local write commands at production;
- assume localhost credentials are safe to commit;
- directly manipulate Elementor serialized data with raw SQL;
- treat Git as a replacement for the local database/media backup.

## 5. What Git Tracks

**Branch split (confirmed 2026-09-07):** the list below describes the
project's `feature/*` development branches (currently `feature/setup`).
`main` is production-deployment-only and holds nothing but
`wp-content/themes/easy-quran-classes-child/` (and
`wp-content/plugins/easy-quran-classes-core/` if ever added) plus its own
`.gitignore` — no docs, no `.claude/`, no local tooling. Never merge a
feature branch into `main`; copy only the production paths across instead
(see `.claude/rules/git.md`).

Track only reproducible project assets such as:
- child theme custom code;
- site-specific plugin custom code;
- Claude configuration/rules/skills;
- project docs;
- optional Docker/local tooling definitions when intentionally used.

Do not track:
- `wp-config.php`;
- DB dumps;
- generated release SQL;
- deployment ZIPs;
- uploads/media;
- cache directories;
- local secrets;
- browser auth state.

## 6. cPanel Audit — Manual / Read-Only First

Because Claude cannot use remote shell, the user should inspect cPanel and provide values/screenshots when needed.

**Confirmed (2026-09-07):** production domain is `https://easyquranclasses.com`; the client confirmed it is genuinely empty — **Mode A (empty/new hosting target)** applies. PHP version confirmed via MultiPHP Manager screenshot: **PHP 8.3** (`ea-php83`); local development is re-pinned to match. Document root and DB availability are still not confirmed.

Collect:
- production domain;
- document root (often `public_html`, but do not assume);
- current WordPress/site state: empty, new install, or existing live site;
- PHP version;
- MariaDB/MySQL availability;
- database name/user state;
- File Manager upload limits;
- phpMyAdmin import limits;
- SSL status;
- current backups / JetBackup availability;
- server-side cache/LiteSpeed/CDN;
- free disk space;
- email/form delivery requirements.

Do not change anything during the first cPanel audit.

## 7. Deployment Modes

### Mode A — Empty/new hosting target

Preferred when there is no production content to preserve.

Typical flow:
1. confirm cPanel backup/rollback baseline;
2. create the production database/user in cPanel;
3. prepare production-ready site files locally;
4. create a serialization-safe SQL export with the production URL;
5. upload a ZIP through File Manager and extract into the confirmed document root;
6. create production `wp-config.php` using cPanel DB credentials, never from Git;
7. import SQL through phpMyAdmin;
8. verify site URL, HTTPS, permalinks, Elementor CSS and forms;
9. remove uploaded archives from the public web root after verification.

### Mode B — Existing production WordPress/site

Do **not** overwrite blindly.

Required before any change:
- full filesystem backup;
- full DB backup;
- inventory of current pages/plugins/theme/uploads;
- explicit decision whether production content must be preserved;
- rollback procedure;
- approved maintenance window if downtime is possible.

If replacement/migration is still appropriate, use a controlled migration plan. A reputable migration plugin may be considered only after `/plugin-evaluation`, but is not mandatory.

## 8. Database URL Migration — Critical Rule

Elementor and WordPress can store serialized data. Do **not** perform broad raw SQL text replacement in phpMyAdmin.

Preferred local release method after the final domain is confirmed:

1. Dry run locally:

```bash
wp search-replace 'http://local.example' 'https://example.com' \
  --all-tables-with-prefix --skip-columns=guid --dry-run
```

2. Generate transformed SQL without mutating the local working database:

```bash
wp search-replace 'http://local.example' 'https://example.com' \
  --all-tables-with-prefix --skip-columns=guid \
  --export='release-production.sql'
```

3. Keep the SQL export outside Git.

Adjust command syntax/path for the actual local environment and URL. Never run this with placeholder URLs.

**Known gap, confirmed 2026-09-07 during the first production deployment**: this
command only catches the *unescaped* form of the URL (`http://local.example`).
Elementor stores page content (`_elementor_data`, `_elementor_element_cache`)
as **JSON with escaped slashes** (`http:\/\/local.example`) — a different byte
sequence the plain search-replace never matches, even though `--all-tables-with-prefix`
covers the table. This is invisible locally (the site always legitimately
runs at the local URL, so there is no mismatch to expose it) and only
surfaces as **mixed-content warnings for specific images/widgets** after a
real domain migration, once the new production URL is live. It does not
affect every image — widgets that reference media by attachment ID resolve
their URL fresh at render time regardless of what's cached in the JSON;
only widgets/fields that bake a literal URL string are affected.

After importing to production, verify and fix it as a mandatory post-import
step, via phpMyAdmin (no shell access, so this must be a manual UI step):

1. **Check** for remaining escaped occurrences (safe: a length/count query, not a raw content dump):
   ```sql
   SELECT COUNT(*) FROM wp_postmeta
   WHERE (LENGTH(meta_value)-LENGTH(REPLACE(meta_value,'http:\\/\\/local.example','')))
         / LENGTH('http:\\/\\/local.example') > 0
   ```
2. **If non-zero**, fix with a literal substring replace — safe here specifically because
   this is JSON, not PHP `serialize()` format, so there is no length-prefix to corrupt:
   ```sql
   UPDATE wp_postmeta SET meta_value = REPLACE(meta_value, 'http:\\/\\/local.example', 'https:\\/\\/example.com')
   WHERE meta_key IN ('_elementor_data','_elementor_element_cache')
   ```
   **Use doubled backslashes** (`\\/`) in the SQL literal — MySQL's string-literal
   parser treats an unrecognized single-backslash escape (`\/`) as "drop the
   backslash," silently turning the pattern back into the plain unescaped URL
   and matching nothing (confirmed: this exact mistake produced "0 rows
   affected" once during this deployment before doubling the backslashes fixed it).
3. Re-run the count query — expect `0`.
4. Re-check the live page for mixed-content console warnings to confirm visually.

This has not yet been folded into a single reliable one-shot WP-CLI command —
a `--regex` pattern matching both forms was attempted and did not work
through this project's Docker Compose invocation layer (untraced backslash
handling through the compose→entrypoint chain); the manual phpMyAdmin
two-step above is the proven, verified process. Revisit a single-command fix
only if it can be verified end-to-end, not assumed from a shorter dry-run.

## 9. File Packaging

Before packaging:
- remove caches;
- remove local-only debug files;
- remove temporary exports/installers;
- ensure no `.env`, local DB credentials or machine paths are included;
- confirm plugin/theme licenses permit deployment;
- preserve uploads required by Elementor/content;
- verify file names/case sensitivity.

Release archives belong in a local `release/` or external staging folder that is ignored by Git.

Do not include old backups inside deployment archives.

## 10. cPanel File Manager

Use File Manager for final transfer because shell access is unavailable.

Preferred method for many files:
1. upload one reviewed ZIP archive;
2. verify the archive name/size;
3. extract it into the exact confirmed document root;
4. inspect extracted structure before deleting/replacing anything;
5. remove the archive after successful deployment.

Do not edit project source code directly in File Manager except for a tiny emergency production fix that has been explicitly approved and then back-ported to Git/local source immediately.

## 11. Database Import

Use cPanel database management + phpMyAdmin.

Before import:
- verify target DB name;
- confirm DB backup if an existing DB is being replaced;
- confirm the SQL file was generated for the production URL;
- check import size limits.

If phpMyAdmin cannot import the release because of hosting limits, stop and choose a safer supported alternative with the host/user. Do not split or manipulate serialized SQL blindly.

## 12. Production `wp-config.php`

Never upload the local `wp-config.php` unchanged.

Production configuration must use cPanel's production DB credentials and production-specific constants.

Do not expose credentials in Claude output or Git.

## 13. Post-Deployment WordPress Checks

After import and file deployment, verify in the browser/wp-admin:
- homepage loads over HTTPS;
- `/wp-admin/` works;
- `home` and `siteurl` are correct;
- Settings > Permalinks can be saved once if rewrite rules need refreshing;
- Elementor > Tools: regenerate CSS/data when needed;
- no mixed-content warnings;
- menus/header/footer load;
- responsive layout matches local QA;
- images load;
- forms submit;
- transactional email path works;
- no PHP/JS console errors;
- SEO plugin canonical/sitemap uses production URL;
- robots/indexing setting is correct for production;
- caches are cleared and then enabled appropriately.

## 14. Backup & Rollback

Before deployment record:
- filesystem backup method and timestamp;
- database backup method and timestamp;
- where each backup is stored;
- how to restore them through cPanel;
- what files/database would be restored if verification fails.

Never delete the last known-good backup during the release.

## 15. cPanel Areas Requiring Explicit Approval

- production File Manager replacement/deletion
- phpMyAdmin import into an existing database
- database/user deletion
- DNS / Zone Editor
- SSL certificates
- Email Routing / MX
- cron jobs
- PHP version/extensions
- broad file permissions
- redirects affecting the whole site
- domain/document-root mapping
- backup retention changes

## 16. MCP / Automation Policy

Do not install a generic cPanel, SSH, filesystem or database MCP server for
this hosting account. No remote shell exists, so such tools do not improve
the approved workflow and may unnecessarily expose account-wide resources.

## 17. Elementor Auto-Installed Companion Plugins (found 2026-09-08)

Visiting Elementor's own admin screens on production silently auto-installed
3 plugins never present on local or in any release archive, all published
by Elementor.com: **Image Optimization** (`image-optimization`), **Web
Accessibility** (`pojo-accessibility`), **Email Deliverability**
(`site-mailer`). None were approved. Treat any future Elementor
admin-screen visit on production as a risk of the same silent install —
check `wp-admin/plugins.php` for new entries afterward.

**Image Optimization cannot be safely deactivated through wp-admin's own
"Deactivate" link on this host.** Root cause (confirmed by reading the
plugin's own source via cPanel's read-only file API): `image-optimization.php`
calls `register_deactivation_hook( __FILE__, [ CoreModule::class,
'on_deactivation' ] )` unconditionally at file scope — outside its own
`plugin_can_start()` requirements gate. This host is missing 3 PHP
extensions the plugin requires (`exif`, `fileinfo`, `gd`), so
`plugin_can_start()` has always returned false and the plugin's real
bootstrap (`plugin.php`, which sets up its async-optimization-queue DB
tables) has never once run. Clicking "Deactivate" still fires the hook,
whose `Module::on_deactivation()` immediately queries that never-created
table via `Async_Operation::get(...)` and fatals on the malformed result —
a genuine bug in the plugin's own code, present specifically because of the
missing extensions, not something to patch (third-party plugin source is
never patched per `CLAUDE.md`). WordPress's fatal-error protection catches
it and shows the standard critical-error page for that one request; because
the `active_plugins` option update happens *after* the deactivation hook
runs in WP core, the crash aborts before the change persists, so the plugin
snaps back to fully active with no lasting damage — safe to hit, but it
will recur every time "Deactivate" is clicked in wp-admin.

**Safe removal path (does not touch the buggy code at all):** WordPress
core's `validate_active_plugins()` (runs automatically on every wp-admin
load) detects when an active plugin's main file has gone missing and calls
`deactivate_plugins( $plugin, true )` itself — the `true` (`$silent`)
argument makes core explicitly skip firing `deactivate_plugin` /
`deactivate_{$plugin}` / `deactivated_plugin` entirely, so
`Module::on_deactivation()` never runs. Procedure, File-Manager-only:
1. Rename `wp-content/plugins/image-optimization` to something like
   `_disabled-image-optimization` (rename, not delete yet — reversible).
2. Load any wp-admin page once. WordPress silently drops it from
   `active_plugins`; confirm on `wp-admin/plugins.php` that it now shows as
   not present / inactive, with no critical error.
3. Once confirmed clean, the renamed folder can be deleted for good via
   File Manager (separate, less-reversible step — confirm before doing it).

This is also the general recovery technique for any plugin whose
deactivation hook fatals — safer than editing the serialized
`active_plugins` value in `wp_options` directly by hand.

**Verified outcome (2026-09-08):** rename-then-reload worked exactly as
predicted — `wp-admin/plugins.php` showed no critical error, Image
Optimization moved to Inactive (its row's Delete link correctly repointed
to `_disabled-image-optimization/image-optimization.php`), and the exact
fatal was confirmed by reading `public_html/error_log` (cPanel File
Manager's read-only `Fileman/get_file_content` UAPI call — safe, no write):
`Uncaught TypeError: call_user_func_array(): ... class
"ImageOptimization\Modules\Core\Module" not found in
wp-includes/class-wp-hook.php`. That log entry's own timestamp and the
stack trace (`wp-toolkit/plib/vendor/wp-cli/...` →
`Plugin_Command->deactivate()`) prove the crash was originally triggered by
**cPanel's own WP Toolkit running `wp plugin deactivate` through its
bundled WP-CLI**, independently of any wp-admin browser click — WP Toolkit
on this account can and does invoke wp-cli plugin actions on its own.
Web Accessibility (`pojo-accessibility`) showed Inactive at the same
check — confirmed by the client screen-sharing cPanel's WP Toolkit
"Plugins" tab: they had manually used its own bulk **Deactivate** button
there, which deactivated Web Accessibility and Email Deliverability
cleanly (no equivalent hook bug) at the same time as the earlier
image-optimization attempt. Worth checking WP Toolkit's own
plugin-management screen for anything else it may act on independently
before assuming production plugin state only changes through wp-admin.

**Final resolution (2026-09-08):** once confirmed Inactive, Image
Optimization's renamed `_disabled-image-optimization` folder was deleted
via wp-admin's own Delete-plugin flow (safe once inactive — WordPress's
`delete_plugins()` only removes files, it fires no activation/deactivation
hooks). Web Accessibility and Email Deliverability were kept installed but
Inactive per client decision (both already dormant, no urgency to remove
files). Production plugin count: 7 (Akismet, Elementor, Email
Deliverability, Fluent Forms, Hello Dolly, Rank Math SEO, Web
Accessibility) — Elementor, Fluent Forms and Rank Math SEO active; the
rest inactive.

**Web Accessibility (`pojo-accessibility`) — decision: leave inactive
(2026-09-08).** Read its `readme.txt` on production. It bundles three
distinct features: (1) a WCAG violation scanner ("Accessibility
Assistant") — genuinely could add supplementary QA value, but this site
already meets the bar the correct way (Lighthouse Accessibility 100,
hand-built semantic HTML/keyboard-nav/focus-states per `CLAUDE.md`); (2) a
floating front-end "Usability widget" (font resize, contrast toggle,
animation pause) — this is the "accessibility overlay" pattern the
professional accessibility community specifically advises against (the
multi-signatory "Overlay Fact Sheet", WebAIM, Deque): overlays don't
achieve real WCAG compliance, can conflict with users' own assistive tech,
and have drawn public legal/advocacy backlash industry-wide (e.g. the
accessiBe controversy); (3) an accessibility-statement generator — low
risk but trivial to write as a static page without a plugin. Full
functionality also requires "Connecting" to Elementor's cloud service
(same account-linking friction as Rank Math/Image Optimization). Net: no
genuine improvement over what the site already does correctly, real
downside from the overlay widget. Left inactive; revisit only if a future
audit finds a specific, concrete accessibility gap the scanner would catch
that Lighthouse/manual review missed.

**Email Deliverability (`site-mailer`) — decision: confirmed real gap,
activation deferred by client decision (2026-09-08).** Tested with a real
Contact-form submission on production (stored correctly in
`wp_fluentform_submissions`) — **no notification email arrived** at the
site's `admin_email` (inbox or spam), confirming the default host PHP
`mail()` path genuinely does not deliver here; this is not a hypothetical
risk. Activating Email Deliverability would fix this, but it requires a
"Connect Your Account" step to Elementor's own hosted mail-relay service —
an external account/data-flow decision only the client can make. **Client
decision: defer activating/connecting this plugin until the real client
email account is ready** (the current `admin_email` is a placeholder/dev
address, not worth connecting a production mail service to yet). Action
for a future session: once the client's real email is set, activate
`site-mailer`, have the client (or whoever holds the Elementor account)
complete the Connect step, then re-run this exact same real-submission
test to confirm delivery before considering it resolved.

Do not automate cPanel login/session capture by default. Claude prepares instructions and release artifacts; the user controls sensitive cPanel actions.

**Scope of the approved MCP servers.** The project's configured MCP servers — `novamira-localhost`
(Claude Code and Codex), `chrome-devtools`, `context7` — are **local-development only**. They
target `http://localhost/` and library documentation, never the production host. Novamira in
particular grants full site/DB/filesystem access and must never be pointed at
`easyquranclasses.com`; `release-check` asserts the Novamira and WPVibe plugins are absent from
every release archive. This carve-out does not loosen the bans above.

## 18. GitHub Actions → FTPS Auto-Deploy Pipeline (built 2026-09-08)

**Confirmed cPanel Git Version Control cannot auto-deploy here**: its own UI
shows "Your system administrator must enable shell access to allow you to
view clone URLs" — it shells out under the hood, and this account has none.
Only "Update from Remote"/manual pull-style operations would ever be
possible even if a repo were created there, which is not true automatic
deployment. This confirms GitHub Actions → FTPS (TASK-DEPLOY.md Phase 8's
documented fallback) is the correct and only viable approach.

**FTP account** (created via cPanel FTP Accounts UI, user typed the
password directly — see `security.md`): login name `theme-deploy`
(`theme-deploy@easyquranclasses.com`), home directory scoped to exactly
`public_html/wp-content/themes/easy-quran-classes-child` — this account
physically cannot reach `wp-config.php`, core, uploads, or any other
plugin/theme. Only the account NAME is documented here; the password lives
only in GitHub Actions Secrets, never in this repo.

**Workflow**: `.github/workflows/deploy.yml`, on `main` only (required —
GitHub Actions only evaluates workflow files from the branch being pushed
to). Triggers on push to `main` touching
`wp-content/themes/easy-quran-classes-child/**`, plus manual
`workflow_dispatch`. Uses `SamKirkland/FTP-Deploy-Action`, pinned to a
commit SHA (not a floating tag), `protocol: ftps`, `dangerous-clean-slate:
false` (never wipes the remote scoped directory first — a stray extra file
there is harmless and recoverable, an accidental wipe is not).
`local-dir`/`server-dir` map the child theme folder straight onto the FTP
account's own chrooted root (`./` on the server side, since the account is
already scoped to exactly that folder).

**GitHub Secrets required** (names only, set via the GitHub repo's own
Settings → Secrets and variables → Actions UI — no `gh` CLI is installed
here, so this is a manual one-time step for whoever holds repo admin):
`FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD`. `FTP_USERNAME` value is the
non-secret login above; `FTP_SERVER` is the account's domain
(`easyquranclasses.com`); `FTP_PASSWORD` is whatever was typed into the
cPanel FTP Accounts form.

**Shared-working-directory hazard hit while building this**: this repo's
working directory was found to be shared live with other concurrent
Claude Code sessions (`git status` changed between two consecutive checks
with no action taken in between — another session had committed directly
to `feature/setup` in the same checkout). Never `git checkout main` in a
checkout that might be shared — it swaps the branch for every session at
once and can destroy another session's uncommitted work. Used `git
worktree add ../<name> main` instead to do all `main`-branch commit/push
work in an isolated directory, leaving the shared `feature/setup` checkout
completely untouched; removed the worktree once done
(`git worktree remove ../<name>`).

## 19. Rank Math Setup Wizard Was Never Completed — Root Cause of 3 Bugs (2026-09-08)

The previously-documented "Rank Math per-page title/meta doesn't render on
production" gap (see `TASK-DEPLOY.md` Phase 7 notes,
`elementor-document-api-gotchas` memory point 17) was never a PHP/server
quirk — Rank Math's own setup wizard had never been completed on this
production install. Symptom that revealed it: **every** Rank Math admin
page ("Sorry, you are not allowed to access this page") and its own sidebar
menu link redirecting to `admin.php?page=rank-math-registration`. This ONE
root cause explains three previously-separate symptoms at once: missing
per-page `<title>`/meta description, missing `sitemap_index.xml` (404), and
the blocked admin pages — all downstream of Rank Math's bootstrap
requiring the wizard's completion flag.

**Fix**: complete the wizard (`Getting Started` → `Easy` mode → `Your Site`
→ skip `Analytics`/Google connection → `Ready`). The `Your Site` step
surfaced two more real bugs while at it: "Website Name" and
"Person/Organization Name" both still read `"Easy Quran Classes (Local)"`
— copied verbatim from local dev's Rank Math settings through the
production DB export/import, never caught because nothing had reached this
far into the wizard before. Fixed by re-typing both to `"Easy Quran
Classes"`; also set site type to "Small Business Site" (was defaulting to
"Personal Blog"). **Check any other Rank Math (or plugin) options tables
for other `(Local)`-suffixed values copied the same way** — this class of
bug (local-only display strings surviving into the production export
verbatim) can recur anywhere a human manually typed a `(Local)`/`(Dev)`
marker into a plugin setting during local build rather than a WordPress
Post/Page field.

**Canonical tag — fully root-caused 2026-09-08, NOT a bug, no fix needed.**
`<link rel="canonical">` is absent from every page on production while
present on local. Root-caused by reading Rank Math's own source
(`includes/frontend/class-head.php`) and confirming live hook state with a
temporary, admin-gated, try/catch-wrapped debug mu-plugin (added and
removed same session, per the established pattern) on **both**
environments:

- `Head::robots()` contains this, by design, citing a real SEO reference
  (comment links to `seroundtable.com/google-noindex-rel-canonical-confusion-26079.html`):
  ```php
  // If a page is noindex, let's remove the canonical URL.
  if ( isset( $robots['index'] ) && 'noindex' === $robots['index'] ) {
      $this->remove_action( 'rank_math/head', 'canonical', 20 );
      $this->remove_action( 'rank_math/head', 'adjacent_rel_links', 21 );
  }
  ```
  And `Paper::respect_settings_for_robots()` unconditionally forces
  `noindex` whenever `get_option('blog_public') == 0` — exactly this
  site's current state. **This is intentional, documented Rank Math
  behavior**: combining `noindex` with `rel=canonical` sends a genuinely
  confusing signal to search engines, so Rank Math omits canonical rather
  than ship both. It will start rendering automatically the moment the
  client approves indexing and `blog_public` is flipped back to `1` — no
  code change, no plugin setting, nothing to fix.
- **Local was never a valid reference for this comparison** — a debug
  dump of `rank_math()`'s own object showed only `version`/`db_version`
  properties set; `rank_math()->frontend` and `rank_math()->head` are
  **never instantiated on local at all** (`$wp_filter['rank_math/head']`
  doesn't even exist as an object). Local's apparently-correct canonical
  and robots meta tags are not coming from Rank Math — they're
  **WordPress core's own native `rel_canonical()` and `wp_robots_noindex()`**
  filling in because Rank Math's Frontend integration never loads there.
  Title/meta description still looked right by coincidence of similar
  output shape, but none of it was exercising Rank Math's real code path.
  **Lesson: before treating local's SEO-plugin output as a trustworthy
  reference again, verify `rank_math()->frontend`/`rank_math()->head` are
  actually set** (e.g. `wp eval 'var_dump(get_object_vars(rank_math()));'`)
  — don't assume matching visual output means the same code ran. Why
  local's Rank Math never fully bootstraps was not chased further (lower
  priority than the production question this was blocking), but is worth
  investigating before relying on local for any other Rank Math behavior
  check.

## 20. Production Backup & Restore Runbook (built 2026-09-08)

The site went live with **no post-deployment backup** — the only prior
account backup (`backup-9.7.2026_13-44-52_easyquranclasses.tar.gz`) predates
the deployment (taken while `public_html` was still empty) and cannot restore
today's content. This section covers the first real backup and how to use it.

### What exists

Two artifacts, both taken via cPanel's native **Files > Backup** page
(no JetBackup on this account — confirmed on the Backup page itself, only
"Full Backup" and "Partial Backups" sections are offered), downloaded
off-server into `local/backups/` (gitignored, never committed — both files
contain live DB credentials via `wp-config.php`/the SQL dump):

| File | What it is | Size |
|---|---|---|
| `local/backups/prod-db-2026-09-08.sql.gz` | `mysqldump` of `easyquranclasses_wp_prod` only, via **Backup > Download a Database Backup** (streams straight to the browser, writes nothing on the server) | 491 KB |
| `local/backups/prod-full-2026-09-08.tar.gz` | **Full Account Backup** (home directory + all DBs + account config), via **Backup > Download a Full Account Backup → Home Directory**. Also still present server-side at `/home/easyquranclasses/backup-9.8.2026_11-25-25_easyquranclasses.tar.gz` for a host-side restore without re-uploading 161 MB. | 161 MB |

Checksums and the full verification trail are in `local/backups/RELEASE-MANIFEST.md`.

**Verification performed, not assumed:** `gzip -t`/`tar -tzf` integrity on
both; confirmed presence of `wp-config.php`, `wp-content/uploads/`, the child
theme, and the DB dump inside the full archive; and — the step that actually
proves it restores — **imported the `.sql.gz` into a throwaway database in
local's own Docker MariaDB** (`eqc_restore_test`, isolated from local's real
`wordpress` DB, not referenced by local's WordPress install) and confirmed:
27 tables (matches the dump's own `CREATE TABLE` count), `siteurl`/`home` =
`https://easyquranclasses.com` (proves it's genuinely production, not a
stale local export), 5 published posts, 10 published pages. That scratch
database was intentionally left in place afterward — cleanup via `DROP
DATABASE` is blocked by the safety classifier (any DROP/TRUNCATE, regardless
of target, per `CLAUDE.md`'s "Never Do" list) and it is harmless sitting
alongside local's real DB in the same container, so it was not worth forcing.
Drop it manually (`DROP DATABASE eqc_restore_test;` via phpMyAdmin or wp-cli
against the local `db` container) next time local's DB is touched, if ever.

### How to restore

**Database only** (e.g. bad data, plugin misconfiguration, safe to keep
current files): cPanel → phpMyAdmin → select `easyquranclasses_wp_prod` →
Import → upload `prod-db-2026-09-08.sql.gz` (phpMyAdmin decompresses `.gz`
automatically) → **this replaces all data in the existing database**, so
per `CLAUDE.md`'s Stop-and-Ask list this step needs explicit approval before
running, and a *fresh* backup should be taken first if any content exists
that isn't already in this dump.

**Full site** (e.g. corrupted files, need to move host, disaster recovery):
extract `prod-full-2026-09-08.tar.gz`'s `homedir/public_html/` contents back
into the document root via File Manager, then import
`mysql/easyquranclasses_wp_prod.sql` from inside the same archive via
phpMyAdmin. cPanel's own UI **cannot** restore a full backup automatically
(confirmed on the Backup page: "You cannot restore full backups through your
cPanel interface") — it must be done manually via File Manager + phpMyAdmin,
the same as the original deployment.

### Cadence recommendation

No automated backup schedule exists on this account ("Account Backups" on
the Backup page explicitly states the server administrator must enable that
feature — not available here). Until/unless that changes, take a fresh pair
of these two backups **before any risky production change** (plugin
activation, bulk content edit, PHP/theme update) and periodically (e.g.
monthly) otherwise, following this same procedure. Do not delete the
2026-09-07 pre-deployment backup or these 2026-09-08 backups when taking new
ones — keep the most recent 2-3 rotations per `CPANEL-WORKFLOW.md` §14's
"never delete the last known-good backup" rule.

## 21. Round 11 Production Update — First Post-Launch Content Sync (2026-09-10)

Production had been live since 2026-09-08 (§18-20) but was never updated
after that first deploy — confirmed by measurement, not assumed: live
served theme `?ver=1.0.0` against local's `1.11.0`; live homepage HTML had
none of the new course/pricing-card or teacher dot-nav markup (10 QA rounds
and 22 `tools/pages/*.php` commits behind); live photos were the pre-
licensing crops (e.g. 34,480 bytes) against local's licensed replacements
(up to 111,678 bytes). This section is the **update deployment** — theme +
database delta + uploads together, not a fresh Mode-A install like §7-13.
Package details/checksums: `local/backups/RELEASE-MANIFEST.md`.

**Sequence actually run, with explicit user approval at each stage:** fresh
full account backup (Backup Wizard, 161.75 MB) → theme released to `main`
via an isolated worktree, FTPS Action confirmed successful → licensed
uploads extracted via File Manager (165 files) → production-URL SQL
imported via phpMyAdmin (110 queries) → the mandatory Elementor JSON-
escaped-URL fix applied (§8; one `UPDATE ... REPLACE()` statement, 193 rows
affected) → account-level NGINX cache cleared. Full verification via plain
`curl` (no cache-bypass tricks — what a real visitor gets, see the gotcha
below) on 6 routes: 0 remaining `http://localhost` references, new markup
classes present, hero image confirmed serving at the licensed 111,678-byte
size, 0 console errors, `noindex, nofollow` confirmed unchanged.

### Gotcha: a browser hard-reload can pass verification while real visitors still see stale content

Chrome's `ignoreCache`/hard-reload sends `Cache-Control: no-cache`, which
this account's NGINX reverse-proxy cache (§ home-page toggle, referenced
throughout §17/§19) honors and bypasses — so a hard-reloaded browser check
can show the post-fix page while a plain request (any real visitor, `curl`,
a search-engine crawler) is still served whatever NGINX cached *before* the
fix. This produced a real false-positive during the Round 11 deploy: a
browser-based check showed the Elementor JSON-escaped-URL fix already live,
but the client independently reported "empty images" — a plain `curl` fetch
confirmed the live homepage was still serving `http://localhost/...` image
URLs. Root cause: the NGINX cache was cleared once, but *before* the URL
fix ran, and repopulated with pre-fix content from the browser checks done
in between. **Lesson: verify any post-deploy fix with a plain, non-cache-
bypassing request (`curl` with no special headers) — not a hard-reloaded
browser — and clear the NGINX cache *after* the last content-changing step,
not before it.**

### Gotcha: phpMyAdmin's own JS is unreliable to drive via CDP-based browser automation

Three different ways of triggering phpMyAdmin action buttons each failed a
different way this round:
- The file-**Import** button's own click handler silently did not submit —
  confirmed via `list_network_requests`: the click registered in the DOM
  (button focus changed) but zero HTTP request fired. **Fix that worked:**
  grab the underlying `<form>` via `document.getElementById('input_import_file').closest('form')`
  and call `form.submit()` directly, bypassing the button's JS entirely.
  This is what actually landed both the 110-query production-content import
  and the follow-up 1-query URL-escape fix.
- The **SQL query console** ("Run SQL query" tab) is unreliable by a
  different failure mode: clicking its own "Go" button, `form.submit()` on
  that form, and `form.requestSubmit(goButton)` each failed differently (a
  silent no-op, a server-side "Incorrect format parameter" from hitting the
  wrong internal route, and a query box that came back empty after
  submission). **Never fought further — the working file-based Import
  method above was reused instead**: write the one-off SQL as a tiny `.sql`
  file, upload it through the same file-input + `form.submit()` path that
  already works. This is the reliable path for any future one-off
  production SQL from an automated session, not the query console.
- A stray, unrelated browser alert ("Missing value in the form!") was left
  open on the phpMyAdmin tab by one of the failed attempts above and blocked
  further navigation on that tab until explicitly dismissed
  (`handle_dialog`) — worth checking for on any phpMyAdmin tab that stops
  responding to navigation.

### CI/CD pipeline — re-verified end-to-end, 4/4 successful runs

Two more live pushes to `main` this round (theme release `1cf7403`, favicon
follow-up `118ea5e`) both triggered `.github/workflows/deploy.yml`
correctly and completed `success` in single digits of seconds — consistent
with the original 2026-09-08 build/test (§18) and its bump/revert
round-trip. Cumulative: 4/4 successful runs across two separate sessions.
The favicon release also incidentally verified the **deletion** side of the
sync (`dangerous-clean-slate: false` still respects file removals via the
Action's own state tracking, not just additions/updates) — `favicon-32.png`
was removed from the theme locally, released, and confirmed `404` on
production afterward, with no other file affected.

### Session-driven cPanel browser automation (new working mode this round)

This round is also the first time a session drove cPanel/phpMyAdmin/File
Manager directly via `chrome-devtools` MCP after the user logged in
themselves (rather than the user clicking through cPanel per §15/§17's
original framing) — an explicit, mid-session user redirect, not a default.
The security boundary held throughout: the session never saw, typed, or
had access to the login credentials (the user typed them directly into the
browser window this session was already controlling, same pattern as the
read-only 2026-09-07 audit); every write action (file upload, SQL import,
cache clear) was narrated and independently verified afterward via
read-only checks against the public site. Treat this as available on
future rounds only when the user explicitly asks for it again, not as the
new default — §15's "user controls cPanel login and sensitive UI actions
by default" still stands as the baseline.
