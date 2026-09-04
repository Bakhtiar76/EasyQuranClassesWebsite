---
description: Evaluates a proposed WordPress or Claude Code plugin/tool before installation for necessity, maintenance, compatibility, access, performance, overlap and security. Use before adding any new plugin, addon, MCP server or significant tool dependency.
---

# Plugin / Tool Evaluation

Before installation, determine:

- exact problem it solves;
- whether native WordPress, Elementor Free, existing project code/tooling, or a smaller custom solution already solves it;
- official/maintainer source and current maintenance status;
- compatibility with the audited environment;
- permissions/data/network/credential access;
- write capabilities;
- performance/runtime overhead;
- overlap with installed tools/plugins;
- free/paid requirement and lock-in;
- removal/rollback path;
- security risk: Low / Medium / High / Critical.

For MCP, additionally report transport, resources/tools exposed and why CLI/project skills are insufficient.

Return one of:
- INSTALL
- DO NOT INSTALL
- NEEDS APPROVAL

Never install nulled, abandoned, unknown-privilege or unreviewed remote software.

## Recorded verdicts

Checked against live sources during the 2026-09-04 Claude Code setup. Re-verify before relying on an old verdict if much time has passed.

- **Novamira** (MCP server, `use-novamira/novamira`) — grants an AI agent arbitrary PHP execution and filesystem access on the connected WordPress site. **DO NOT INSTALL** on this project's hosting. Violates the "no site-side agent plugin without approval" rule in `.claude/rules/security.md`. Reconsider only on disposable staging with explicit written approval, never production.
- **WPVibe / `vibe-ai`** (SeedProd, wordpress.org, 10k+ installs) — site-side MCP plugin; can edit theme files, run WP-CLI and database queries, and routes access through third-party `wpvibe.ai`. **NEEDS APPROVAL, staging only.** Not for production.
- **WordPress.com connector** (Automattic, claude.com/connectors) — works only with WordPress.com / Jetpack-connected sites, not a self-hosted cPanel install. **N/A** for this project unless the site later becomes Jetpack-connected.
- **`WordPress/agent-skills`** (official WordPress org GitHub repo) — legitimate, but most of its 17 skills target Gutenberg block themes/Playground, which conflicts with this project's Elementor + Hello Elementor child-theme architecture, or duplicate skills already in `.claude/skills/`. Only `wp-plugin-development` (hooks, settings API, PHP security) was adopted, installed by reviewing and copying its files rather than running its `npx skills add` installer.
