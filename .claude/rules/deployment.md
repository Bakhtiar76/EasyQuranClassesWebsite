# Deployment Rules

- Hosting has no Shell/SSH access. cPanel deployment is manual and approval-gated.
- Read `CPANEL-WORKFLOW.md` before release work.
- Never assume `public_html`; confirm document root.
- Back up production filesystem + DB before replacement/import.
- Generate production URL SQL locally using serialization-safe WP-CLI search-replace export; do not mutate production with raw SQL replacement.
- Keep release archives/SQL outside Git.
- User controls cPanel login and sensitive UI actions by default.
- File Manager + phpMyAdmin are deployment tools, not source-editing workflow.
- After deployment verify HTTPS, URLs, permalinks, Elementor CSS/data, forms, images, SEO/indexing and caches.
- Remove public deployment archives/installers after successful release.
- BugDrop is local-development-only. It belongs only in `local/mu-plugins/`; never copy it into the child theme, a production package, or `main`. Before any production `main` push, fail the release if `git grep -n -i bugdrop main -- wp-content/themes/easy-quran-classes-child` returns a match or if the packaged theme contains a BugDrop script/reference.
