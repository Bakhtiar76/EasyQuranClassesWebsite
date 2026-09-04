# Easy Quran Classes — cPanel / Hosting Workflow

## Goal

Use cPanel as hosting infrastructure, not as the primary code editor.

Preferred model:

Local Git + Claude Code
→ protected staging WordPress
→ verified production release

Elementor content/database changes and custom-code deployment are treated separately.

## Preferred cPanel Capabilities

Audit whether the account exposes:

- WP Toolkit
- Git Version Control
- SSH Access / Terminal
- MultiPHP Manager
- SSL/TLS Status
- Backup / JetBackup or host backup product
- Cron Jobs
- Zone Editor
- Email Routing / Email Accounts
- Metrics / Errors

Do not change anything during the first audit.

## Staging

Preferred options, in order:

1. WP Toolkit staging/clone if the host provides it and it is reliable
2. Host-provided staging tool
3. Dedicated staging subdomain and cloned WordPress installation

Staging must be protected from accidental indexing using appropriate WordPress/search-engine settings and, ideally, authentication at the web-server/cPanel layer.

## Code Deployment

Git is for custom code only:

- child theme
- site-specific plugin
- scripts/config/docs

Do not use Git as the backup mechanism for:

- Elementor layouts stored in the database
- posts/pages
- WordPress settings
- plugin settings
- media uploads

If cPanel Git Version Control is available, it may be used for a controlled custom-code deployment workflow.

Do not deploy the entire WordPress root from Git unless the architecture has intentionally been designed for that model.

## SSH

Use SSH keys, not passwords, when the host permits it.

Important:

- a cPanel account SSH key may grant broad access to the hosting account
- keep the private key outside the repository
- use a dedicated key for this client/project when practical
- protect the key with a passphrase
- do not let Claude print or read private-key contents

## WP-CLI

Use WP-CLI for repeatable WordPress inspection and approved changes.

Useful read-only checks include:

```bash
wp core version
wp core verify-checksums
wp plugin list
wp theme list
wp option get home
wp option get siteurl
wp rewrite structure
wp cron event list
```

Do not run commands that reveal DB credentials.

High-impact commands such as search/replace, imports, deletes or mass updates require backup + dry-run where available + explicit approval.

## Backups

Before major changes confirm both:

- filesystem/site backup
- database backup

Record:

- backup mechanism
- timestamp
- storage location
- whether the backup is outside the public web root
- restore method

Do not assume a backup is useful merely because a file exists. Verify it is recent and non-empty, and know how it would be restored.

## Production Release

Before production:

- staging QA complete
- Git diff reviewed
- no secrets in repository
- DB/site backup confirmed
- maintenance window considered if needed
- form/email path tested
- rollback defined

Deploy custom code first when possible, then make controlled database/content changes.

Avoid blind whole-site overwrite workflows.

## cPanel Areas Claude Must Not Change Without Explicit Approval

- DNS / Zone Editor
- SSL certificates
- Email Routing / MX
- email account passwords
- cron jobs
- PHP version/extensions on production
- directory ownership/permissions broadly
- redirects affecting the whole site
- backup retention settings
- domain/document-root mapping

## Recommended Access Pattern

For normal development:

- browser: WordPress/Elementor on staging
- terminal: SSH/WP-CLI on staging
- source control: Git locally + remote repository
- cPanel UI: hosting administration and verified deployment/staging features

Do not use cPanel File Manager as the normal code-editing workflow.
