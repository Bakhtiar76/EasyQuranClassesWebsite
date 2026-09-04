# WordPress Rules

- Never modify WordPress core.
- Never permanently edit third-party plugin source or the Hello Elementor parent theme.
- Prefer native WordPress and Elementor Free before adding plugins.
- Keep business functionality out of the presentation theme when it should survive a theme change.
- Sanitize input, validate boundaries, escape output, use nonces/capability checks where relevant.
- Use WP-CLI read commands for discovery when available.
- Do not directly rewrite Elementor `_elementor_data` during normal development.
- The blog uses native WordPress Posts.
- Do not create fabricated business facts, testimonials, prices or teacher credentials.
