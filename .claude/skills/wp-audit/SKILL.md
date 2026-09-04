---
description: Performs a safe read-only audit of a WordPress installation and reports architecture, versions, plugins, themes, URLs, risks and recommended next actions. Use during discovery, before major changes, or when the current WordPress state is uncertain.
---

# WordPress Audit

Perform an inspection-only audit.

## Safety

- Confirm whether the target is local, staging or production before connecting.
- Do not modify files, options, content, plugins, themes or database data.
- Do not read or print DB passwords, salts, private keys or unrelated secrets.
- Do not install tools.
- If remote access is not already configured, stop and explain what access is needed.

## Inspect

When available, use safe read commands such as:

- WordPress version and checksum state
- PHP version
- active/inactive plugins and versions
- installed themes and active theme
- `home` and `siteurl`
- permalink structure
- cron list/state
- multisite state
- debug state without exposing secret values
- presence of Elementor / Hello Elementor
- presence of caching, SEO, security, backup, SMTP and form plugins

Also inspect repository/project structure if local files are available.

## Report

Return:

1. Target/environment
2. WordPress/PHP state
3. Theme state
4. Plugin inventory grouped by purpose
5. Elementor state
6. SEO/cache/security/backup/form observations
7. Architecture risks
8. Plugin duplication/bloat concerns
9. Missing prerequisites
10. Recommended next action
11. Commands executed

Explicitly state that no changes were made.
