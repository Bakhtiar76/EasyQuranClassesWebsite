# Easy Quran Classes — Codex Instructions

This file is for the **Codex CLI**. Claude Code reads `CLAUDE.md`; the two agents share one
rulebook so they never drift. Everything in the files below applies to you **verbatim** — this
file only adds Codex-specific operating notes on top.

---

## MANDATORY: read these first, in full, before any work

1. **`CLAUDE.md`** — the complete project rulebook (stack, boundaries, "Never Do", workflow).
2. **`.claude/rules/*.md`** — six focused rule files: `security.md`, `git.md`, `wordpress.md`,
   `implementation.md`, `design.md`, `deployment.md`.
3. **`DESIGN.md`** — required before any frontend / Elementor / CSS work.
4. **`CPANEL-WORKFLOW.md`** — required before any release / deployment work.

Also useful: `README-SETUP.md` (machine setup + the known-issues table), `TOOL-INVENTORY.md`
(what is installed and why), `local/README.md` (Docker + WP-CLI operations).

There is no separate "Codex rulebook" — if something here is silent, `CLAUDE.md` governs.

---

## Codex-specific operating notes

### Docker / WP-CLI must run OUTSIDE the sandbox

The local WordPress site runs in Docker, and **Docker is unreachable from inside Codex's Windows
sandbox** — confirmed in both `--sandbox read-only` and `--sandbox workspace-write`:

```
permission denied while trying to connect to the docker API at npipe:////./pipe/docker_engine
Access is denied.  (on ~/.docker/config.json)
```

Consequences:

- Any `docker …` / `docker compose …` / `local\wp.ps1 …` command needs approval to run
  **escalated / outside the sandbox**. Ask for it; don't retry sandboxed.
- **Never conclude the stack is down from a sandboxed Docker error.** Verify with an
  out-of-sandbox `docker compose -f local/docker-compose.yml ps` or `curl http://localhost/`.

### Use `npx.cmd`, not `npx`

Bare `npx` in PowerShell is blocked here: `npx.ps1 cannot be loaded because running scripts is
disabled on this system`. `npx.cmd` works. All MCP servers below launch via `npx.cmd`.

### `codex mcp list` fails inside a nested sandbox

`Error: failed to resolve CODEX_HOME / Could not find home directory` — the sandbox strips the
home env. Run `codex mcp list` from a normal terminal, outside any `codex exec` sandbox.

### MCP servers available in this project

Registered globally in `~/.codex/config.toml` by `tools/codex/setup-codex.ps1`:

| Server | Purpose |
|---|---|
| `novamira-localhost` | WordPress control plane for the **local** site — PHP eval incl. `$wpdb`, WP-CLI, filesystem R/W, Elementor `Document::save()`. Launched via `tools/codex/novamira-mcp.cmd` so the Application Password is never written into config. Local only — never production. |
| `chrome-devtools` | Screenshots, console, network, Lighthouse against `http://localhost/`. Backs the `visual-qa` and `performance-audit` skills. |
| `context7` | Live WordPress / Elementor / PHP library docs. No auth. |

Codex's bundled `browser@openai-bundled` and `computer-use` plugins are already enabled and are
the interactive browser-automation path; the scripted path is `node tests/visual/sweep.mjs`
(project-local 7-viewport runner — prefix with `MSYS_NO_PATHCONV=1` in Git Bash).

### Project skills

`tools/codex/setup-codex.ps1` syncs every `.claude/skills/<name>/SKILL.md` into
`$CODEX_HOME/skills` as `eqc-<name>` (junction where possible, copy as fallback). The
**canonical source is `.claude/skills/`** — edit there, then re-run the script. Do not edit the
synced copies.

### Production boundary (same as `CLAUDE.md`)

cPanel hosting has **no shell/SSH**. Never point any tool, MCP server, or browser session at
`easyquranclasses.com`. Production deployment is manual, approval-gated, and done by the user
through the cPanel UI — see `CPANEL-WORKFLOW.md`.

### Git

Short, natural, one-line commit messages. **No AI-attribution trailers** (`Co-Authored-By`,
"Generated with…", etc.) — `.claude/rules/git.md` and the `guard-bash` hook both enforce this.
Do not push without explicit authorization.
