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

**Remaining unresolved gap**: `<link rel="canonical">` is still completely
absent from every page (homepage, pages, posts) even after the wizard and
a Permalinks re-save — confirmed present and correct on local for the same
pages, so this is a genuine production-only regression, not expected
behavior. Low urgency while the site stays `noindex` (canonical has no
effect on a page search engines won't index anyway), but must be
root-caused and fixed before public launch — a missing canonical risks
duplicate-content signals across `http`/`https`, apex/`www`, or
trailing-slash variants once indexing is enabled. Not yet investigated
past ruling out the wizard/permalinks as the fix.
