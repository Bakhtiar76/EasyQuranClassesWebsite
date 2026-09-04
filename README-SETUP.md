# Easy Quran Classes — Claude Code Starter Pack

## Files

- `TASK.md` — one-time professional Claude Code environment/setup task
- `DESIGN.md` — visual source of truth
- `CLAUDE.md` — persistent Claude Code project instructions
- `CPANEL-WORKFLOW.md` — hosting and deployment guardrails
- `.claude/rules/` — focused always-on project rules, including implementation and Git workflow rules
- `.claude/skills/` — reusable WordPress/cPanel/QA workflows
- `.gitignore` — starting ignore rules; review against the actual repository before committing

## Recommended Placement

Place the contents of this pack at the root of the Easy Quran Classes Git repository.

Do not place the repository in a directory containing unrelated client credentials or private files.

## First Claude Code Check

After placing the files:

1. Start Claude Code from the repository root.
2. Run `/context` and confirm `CLAUDE.md` is loaded.
3. Confirm project skills appear.
4. Do not connect Claude to production yet.
5. Run `/wp-audit` locally or against an explicitly approved staging target.

## Browser QA Tooling

When Node.js 20+ is confirmed, Playwright CLI is the preferred browser-automation tool for this project.

Suggested installation after approval:

```bash
npm install -g @playwright/cli@latest
playwright-cli install --skills
```

Use browser automation primarily against staging.

## MCP Policy

Start with no third-party MCP servers.

Do not connect a generic cPanel, SSH, filesystem or database MCP server to the client hosting account.

If an official WordPress MCP integration becomes useful later, test it on staging with least privilege before considering broader access.

## Tool Inventory

Audited 2026-09-04. Windows 11 native, Git Bash primary shell (no WSL/pwsh installed).

| Tool / Skill / Plugin / MCP | Version/source | Scope | Purpose | Access | Risk | Status |
|---|---|---|---|---|---|---|
| Claude Code | 2.1.260 | machine | agent CLI | filesystem/shell (permission-gated) | — | Active |
| Git | 2.51.0.windows.1 | machine | version control | repo | Low | Active |
| Node.js | v24.11.0 | machine | hooks runtime | local | Low | Active |
| npm | 11.6.1 | machine | package installs (rare, ask-gated) | network | Low | Active |
| PHP | — | machine | not installed | — | — | Deferred — no PHP in repo yet; install when child theme/plugin work starts |
| Composer | — | machine | not installed | — | — | Deferred, same as PHP |
| WP-CLI | — | machine | not installed | — | — | Deferred — no WordPress install/hosting yet |
| ssh | OpenSSH (Windows) | machine | future cPanel access | remote host (ask-gated) | Medium | Configured only when hosting exists; dedicated key outside repo |
| gh (GitHub CLI) | — | machine | not installed | — | — | Skipped — local commits don't need it; revisit if PR workflow starts |
| Lighthouse (standalone) | — | — | not installed | — | — | Not needed — chrome-devtools MCP's `lighthouse_audit` covers this |
| Playwright CLI | — | — | not installed | — | — | Deliberately skipped — chrome-devtools MCP already covers browser QA; avoids a duplicate browser stack (~500MB) |
| chrome-devtools MCP | official Anthropic plugin | project | browser QA, console/network, Lighthouse, perf traces | localhost/staging browser | Low | Active — sole browser QA tool for this project |
| context7 MCP | official Anthropic plugin | project | up-to-date library/API docs lookup | read-only web docs | Low | Active |
| Project skills (13, see `.claude/skills/`) | local | project | WordPress/Elementor/cPanel/QA workflows | filesystem | Low | Active, reused from starter pack |
| `wp-plugin-development` skill | WordPress/agent-skills (official), reviewed and copied | project | plugin hooks/settings-API/security patterns | filesystem | Low | Added — see `.claude/skills/plugin-evaluation/SKILL.md` for why only this one of 17 upstream skills was adopted |
| `.claude/hooks/guard-bash.mjs` | local | project | PreToolUse: blocks root-wipe, raw DROP/TRUNCATE, curl\|sh, secret-file reads, AI-attribution commits | none (reads command text only) | Low | Active, tested |
| `.claude/hooks/post-edit-validate.mjs` | local | project | PostToolUse: JSON parse + PHP lint on edited files | filesystem (read-only) | Low | Active, tested |
| `.claude/settings.json` | local | project | permission deny/ask/allow rules, plugin toggles | — | — | Active |
| Novamira (MCP) | third-party | — | not installed | full PHP execution + filesystem on site | Critical | DO NOT INSTALL — see plugin-evaluation verdicts |
| WPVibe / vibe-ai (WP plugin+MCP) | SeedProd | — | not installed | theme edit, WP-CLI, DB, third-party SaaS | High | Needs approval, staging only — see plugin-evaluation verdicts |
| WordPress.com connector | Automattic | — | not installed | N/A (WordPress.com/Jetpack only) | — | Not applicable to this self-hosted site |
| cPanel/SSH/DB MCP (generic) | — | — | not installed | account-wide | Critical | Not installed by policy — see `TASK.md` §12 |

Update this table, not a second file, as tooling changes.
