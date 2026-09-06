---
name: wp-audit
description: Audit the approved local Easy Quran Classes WordPress installation before implementation.
---
# WP Audit

Use only against the confirmed local site.

1. Identify local WordPress root/URL and prove it is not production.
2. Inspect WordPress/PHP version, active theme, installed plugins, permalink structure and Elementor/Hello state.
3. Prefer read-only WP-CLI commands first.
4. Check obvious warnings/errors without exposing credentials.
5. Report reusable existing functionality before recommending additions.
6. Do not change plugins/themes/options during the audit unless separately approved.
7. Never attempt remote WP-CLI/SSH; hosting has no shell access.
