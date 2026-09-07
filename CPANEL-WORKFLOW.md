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

Do not install a generic cPanel, SSH, filesystem or database MCP server for this hosting account.

No remote shell exists, so such tools do not improve the approved workflow and may unnecessarily expose account-wide resources.

Do not automate cPanel login/session capture by default. Claude prepares instructions and release artifacts; the user controls sensitive cPanel actions.

**Scope of the approved MCP servers.** The project's configured MCP servers — `novamira-localhost`
(Claude Code and Codex), `chrome-devtools`, `context7` — are **local-development only**. They
target `http://localhost/` and library documentation, never the production host. Novamira in
particular grants full site/DB/filesystem access and must never be pointed at
`easyquranclasses.com`; `release-check` asserts the Novamira and WPVibe plugins are absent from
every release archive. This carve-out does not loosen the bans above.
