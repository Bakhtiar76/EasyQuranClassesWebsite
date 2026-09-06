# Security Rules

- This project is local-first. Production cPanel has no shell/SSH access.
- Never probe or configure remote SSH/WP-CLI/rsync unless the user later confirms hosting changed.
- Never read, print, log or commit passwords, DB credentials, API keys, private keys or browser auth state.
- Never commit `.env`, `wp-config.php`, SQL dumps, backups, release ZIPs or auth/session files.
- Treat any unknown domain/remote target as production.
- Do not automate cPanel login/session capture by default.
- No generic cPanel, SSH, unrestricted filesystem or direct database MCP servers.
- Require approval before production file/database replacement, DNS/SSL/email/PHP changes or destructive operations.
- Never use raw SQL broad search/replace on WordPress/Elementor serialized data.
- Do not install nulled software or execute unreviewed downloaded scripts.
