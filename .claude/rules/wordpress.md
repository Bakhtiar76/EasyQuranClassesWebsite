# WordPress Rules

- WordPress and Elementor development happens on the approved local site.
- Prefer native WordPress + Elementor Free before custom code/plugins.
- Never modify WordPress core or Hello Elementor parent theme.
- Put presentation code in the child theme and persistent business functionality in a site plugin only when needed.
- Do not directly edit `_elementor_data` during normal implementation.
- Use local WP-CLI for inspection, plugin/theme operations, backups and safe exports.
- Validate/sanitize input, escape output, use nonces/capability checks and enqueue assets properly.
- Blog content uses native WordPress Posts.
- Do not invent business facts/testimonials/pricing/teacher claims.
- Full site state includes DB + uploads; Git alone is not a WordPress backup.
