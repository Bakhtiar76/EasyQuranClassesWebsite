---
description: Performs a non-destructive cPanel/hosting audit for a WordPress project, covering domain, SSL, PHP, storage, backups, SSH, Git, WP Toolkit, caching, cron and email/DNS dependencies. Use before designing staging/deployment or when hosting capabilities are unknown.
---

# cPanel / Hosting Audit

This skill is audit-only by default.

## Safety

- Do not change cPanel settings.
- Do not alter DNS, SSL, email routing, cron, PHP version, permissions or document roots.
- Do not reveal cPanel passwords, SSH private keys, DB credentials or API tokens.
- If SSH is used, first confirm hostname, account and environment.
- Do not inspect unrelated home-directory content.

## Determine

Using the cPanel UI information supplied by the user and safe SSH commands when approved, determine:

- primary domain/document root
- server/web stack if visible
- PHP version and relevant limits
- WordPress path
- WP-CLI availability
- WP Toolkit availability
- Git Version Control availability
- SSH/Terminal availability
- SSL status
- storage/free disk space
- backup product and most recent backup state
- host/server caching layer
- CDN/Cloudflare status if visible
- cron state relevant to WordPress
- staging capability
- email/SMTP dependencies that could affect forms

## Report

Return:

- capabilities available
- capabilities missing
- risk observations
- recommended staging strategy
- recommended deployment strategy for custom code
- recommended backup/rollback strategy
- whether SSH should be enabled/used
- whether cPanel Git is useful for this account
- exact next action

Explicitly state that no hosting settings were changed.
