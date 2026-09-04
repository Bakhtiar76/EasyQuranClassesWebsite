# Easy Quran Classes — Claude Code Instructions

## Project
Easy Quran Classes is an English WordPress marketing/enrollment website for online Quran education.

Client-provided screenshots are the primary visual reference at "Assets" Folder.
Riwaq Al Quran (https://riwaqalquran.com) is UX/content inspiration only and must not be cloned.


Target stack:
- WordPress
- Elementor Free
- Hello Elementor parent theme
- small Easy Quran Classes child theme for presentation code
- site-specific plugin only if business functionality genuinely requires one
- native WordPress Posts for the blog

Client screenshots are the primary visual reference. Riwaq Al Quran is UX/content inspiration only and must not be cloned. Before frontend or Elementor work, read `DESIGN.md`.

## Core Method
Inspect first. Change second. Verify third.

Prefer the simplest native WordPress/Elementor solution that satisfies the requirement. Do not over-engineer or refactor unrelated code. Never guess environment, paths, WordPress state, plugin/theme state, database state or hosting capabilities when they can be safely inspected.

## Environment Safety
Treat every unknown remote target as production until explicitly confirmed.

Before any remote write, report:
- hostname/domain
- WordPress root/current directory
- environment: local, staging or production
- intended change
- rollback method

Do not connect to unknown SSH hosts, read credentials from unrelated files, or use bypass-permissions mode.

## Production Gate
Do not make production changes unless the target is identified, filesystem/site backup and DB backup are confirmed, rollback is understood, staging testing was completed when practical, and the user explicitly approves the production operation.

## Stop and Ask
Request approval before:
- production database writes or `wp search-replace`
- DB import/reset or destructive repair
- deleting WP content/plugins/themes/uploads
- DNS, SSL/TLS, email-routing or cron changes
- broad permission/ownership changes
- production deployment or destructive shell commands
- force push or overwriting backups

Show the exact proposed command/change, impact and rollback first.

## Never Do
Never:
- modify WordPress core
- edit Hello Elementor parent theme
- permanently patch third-party plugin source
- directly rewrite Elementor `_elementor_data` during normal development
- drop/truncate/reset database tables
- hardcode/log credentials or read SSH private-key contents
- commit `wp-config.php`, `.env`, keys, DB dumps or backups
- install nulled software or execute downloaded scripts blindly
- disable SSL/security controls merely to make something work
- modify unrelated files outside project scope

## Architecture
Expected custom-code locations:
- `wp-content/themes/easy-quran-classes-child/`
- `wp-content/plugins/easy-quran-classes-core/` only if required

If the audited site uses a different safe architecture, report it before changing anything. Theme owns presentation. A site plugin owns business functionality that should survive a theme change. Elementor owns page content/composition, containers, responsive layout and supported global styles.

## Elementor
Use Elementor Free containers/flexbox. Prefer global colors/typography, reusable classes, native widgets and child-theme CSS for reusable styling Elementor Free cannot express cleanly.

Avoid spacer-based layouts, arbitrary margins, excessive negative margins, addon-pack bloat and direct Elementor DB manipulation.

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
Do not invent student/review counts, testimonials, teacher identities, qualifications, certifications, experience, ratings, prices, guarantees, accreditation or business statistics. Use clearly marked staging placeholders when verified information is unavailable. Never knowingly publish placeholders as facts.

## SEO / Accessibility / Performance
Maintain one appropriate H1, logical H2/H3 hierarchy, clean permalinks, useful internal links, valid canonicals/meta and factual schema only. Do not run competing SEO plugins.

Preserve semantic HTML, keyboard navigation, focus states, labels, contrast, alt text and reduced-motion behavior where custom motion exists.

Before adding performance plugins, inspect server caching/CDN, images, fonts, Elementor DOM and third-party scripts. Prefer properly sized AVIF/WebP, limited font weights and minimal JS.

## Git
Git tracks custom code/project configuration, not the full mutable WordPress site. Never commit uploads, caches, `wp-config.php`, `.env`, keys, database dumps or backups.

Before commits:
- inspect `git status`
- review relevant diff
- verify no secret/generated files were added
- run applicable checks

Do not push unless the user asks or the milestone explicitly includes it.

## cPanel / SSH
Prefer professional workflows over cPanel File Manager. Use, when available, WP Toolkit for WordPress/staging management, cPanel Git Version Control for custom-code deployment, SSH for controlled CLI access, and WP-CLI for inspection/approved changes.

cPanel SSH may expose the full hosting account. Use least privilege and keep keys/passwords outside the repository. Do not assume `wp-toolkit` CLI is available to a shared-hosting account.

## WP-CLI
Prefer read-only WP-CLI during discovery. Never reveal DB credentials from `wp-config.php` merely to prove access.

Before a WP-CLI write:
- confirm environment
- state affected data
- define rollback
- request approval when production/high-impact
- use `--dry-run` when supported

## Verification
A file change is not completion. Verify with the relevant combination of browser, WordPress admin, Elementor, WP-CLI read checks, PHP lint, browser console/network, Playwright, Lighthouse and responsive screenshots. If verification was not possible, say so.

## Task Workflow
1. State goal.
2. Inspect current state.
3. Identify files/data affected.
4. State risk level.
5. Implement the smallest correct change.
6. Review diff/state.
7. Test and verify.
8. Report manual checks still required.
9. Suggest a Git commit message when appropriate.

## Tooling
Primary environment: Windows 11, Git Bash (no WSL/PowerShell Core installed). Node.js is available and runs the project's Claude Code hooks; PHP, Composer and WP-CLI are not yet installed locally — install them when the child theme/site plugin work actually begins.

Browser QA and performance auditing use the chrome-devtools MCP exclusively (screenshots, console/network, accessibility, `lighthouse_audit`, performance traces) — no separate Playwright CLI or Lighthouse install for this project, to avoid duplicate browser stacks.

Permission rules and safety hooks live in `.claude/settings.json` and `.claude/hooks/` (`guard-bash.mjs`, `post-edit-validate.mjs`); see `README-SETUP.md` for the full tool inventory and `.claude/skills/plugin-evaluation/SKILL.md` for recorded verdicts on external WordPress AI tools (Novamira, WPVibe, WordPress.com connector).

## Final Report
Always report:
- summary
- environment affected
- files/data changed
- commands executed
- verification performed
- manual verification remaining
- risks/open questions
- suggested Git commit message
