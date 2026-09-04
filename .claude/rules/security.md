# Security Rules

- Treat unknown remote targets as production.
- Never expose credentials, private keys, tokens, DB passwords or cookie/session secrets.
- Never commit secrets, backups or database dumps.
- Never read SSH private-key contents.
- Stop before destructive/high-impact operations and request approval with exact command + rollback.
- Do not modify DNS, SSL, email routing, cron, broad permissions or production DB state without explicit approval.
- Prefer least privilege and staging-first workflows.
- Do not install nulled or unverified software.
- Secrets live only in a local, gitignored `.env` (see `.env.example` for the expected keys). Never place real values in chat, commits, logs, skills, or documentation.
- Do not install a site-side WordPress agent/automation plugin (e.g. one granting arbitrary PHP execution or broad WP-CLI/DB access to an external AI service) on production without explicit approval and staging verification first. See `.claude/skills/plugin-evaluation/SKILL.md` for recorded verdicts on specific tools.
