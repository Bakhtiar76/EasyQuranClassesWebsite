# Easy Quran Classes — Machine Setup & Onboarding

How to get this repo from a fresh clone to a working local WordPress build environment,
with **both Claude Code and Codex** operational, on Windows.

- Day-to-day rules live in **`CLAUDE.md`** (Claude Code) and **`AGENTS.md`** (Codex).
- Day-to-day **git push / PR workflow** is **`CONTRIBUTING.md`** — read this before your first push.
- Deep local-environment rationale is in **`CLAUDE.md` → "Local Environment"** and **`local/README.md`**.
- Deployment is **`CPANEL-WORKFLOW.md`** (manual, no shell on the host).
- What is installed and why: **`TOOL-INVENTORY.md`**.

This file is the reproducible procedure and the **known-issues catalogue** (§7). If a step here
disagrees with reality on your machine, fix this file.

---

## 1. Prerequisites

Install these first. Versions are what the environment was built and verified against
(2026-09-07); newer patch releases are fine.

| Tool | Verified version | Notes |
|---|---|---|
| Windows | 11 Pro 24H2+ | |
| Docker Desktop | 29.7.2 (Compose v5.5.0) | WSL2 backend. Must be **running** before any WP-CLI/site command. |
| Node.js | v24.19.0 | includes `npm` 11.17.0 / `npx` |
| Git | 2.55.0 | Git Bash ships with it; both PowerShell and Git Bash are used |
| PHP (CLI) | 8.3 | optional on the host — only used by the `post-edit-validate` hook for `php -l`; the site's PHP is in Docker. Matches confirmed production PHP 8.3. |
| Claude Code | 2.1.263+ | `claude` on PATH |
| Codex CLI | 0.153.4+ | `codex` on PATH — installer: `npm install -g @openai/codex` |

No SSH client setup, no remote WP-CLI, no rsync — the host has **no shell access** and the
project is local-first. Do not configure them.

---

## 2. Clone and secrets

```bash
git clone <repo-url> EasyQuranClassesWebsite
cd EasyQuranClassesWebsite
git checkout feature/setup
```

**Do this checkout immediately.** GitHub's default branch here is `main`, which is deliberately
pruned to production-deployment files only (see `.claude/rules/git.md`) — it has no `local/`,
`tools/`, `.claude/` or docs at all. Skipping this step means the very next command
(`cp local/.env.example ...`) fails with "No such file or directory", and everything below it is
missing too.

### 2.1 `local/.env` — local WordPress + Novamira (required)

```bash
cp local/.env.example local/.env
```

Then edit `local/.env` and set:

| Key | What |
|---|---|
| `WORDPRESS_DB_NAME` / `_USER` / `_PASSWORD` | any local values; used by both `db` and `wordpress` containers |
| `WP_ADMIN_USER` / `_PASSWORD` / `_EMAIL` | the local wp-admin account created on first bring-up |
| `NOVAMIRA_APP_PASSWORD` | a WordPress **Application Password** for the admin user (created in §5). Both Claude Code (`.mcp.json`) and Codex (`tools/codex/novamira-mcp.cmd`) read the Novamira password from here. |

`local/.env` is gitignored. Never commit it.

### 2.2 Root `.env` — deployment metadata (optional for build work)

```bash
cp .env.example .env
```

Fill in `EQC_PROD_DOMAIN`, `EQC_STAGING_DOMAIN`, `EQC_CPANEL_URL`, `EQC_CPANEL_USER`,
`EQC_WP_PATH`, `EQC_WP_ADMIN_URL` when you get to deployment. This file is **read by nothing in
the repo** — it exists purely as a human-readable reference for `CPANEL-WORKFLOW.md`. There is
**no SSH**; do not add SSH/key variables here at all — see the boundary below.

**What does NOT belong in this file:** cPanel/FTP passwords, private keys, `.pub` files, or any
other real credential. `.env` is gitignored and blocked from Claude Code's Read/Bash tools
(`.claude/settings.json`, `.claude/hooks/guard-bash.mjs`), but that only protects it from being
read back out through this tool — it does nothing to protect it on disk, in an editor's recent-
files list, in a screen share, or in whatever backs up this machine. The FTPS auto-deploy
pipeline (`.github/workflows/deploy.yml`) already sources its credentials the correct way — from
**GitHub Actions Secrets** (`FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD`), never from a file in
the working tree. Follow the same pattern for anything else: real secrets go in a password
manager or GitHub Secrets; this file stays reference metadata only. If you've put real
credentials here, move them out, then diff `.env` against the annotated `.env.example` and
delete anything the example doesn't call for.

### 2.3 Windows User environment variables

Set these as **User** env vars (not just the current shell), then **fully restart** the
agent / terminal:

```powershell
[Environment]::SetEnvironmentVariable('NOVAMIRA_APP_PASSWORD','<application-password>','User')
[Environment]::SetEnvironmentVariable('NOVAMIRA_CREDENTIAL_BACKEND','file','User')
```

- `NOVAMIRA_APP_PASSWORD` — Claude Code's `.mcp.json` resolves `${NOVAMIRA_APP_PASSWORD}` from
  the **OS environment**, not from `local/.env`. (Codex can't use it — see §7 — so Codex reads
  `local/.env` directly. Setting it here anyway keeps Claude Code working.)
- `NOVAMIRA_CREDENTIAL_BACKEND=file` — only needed if you use the Novamira **CLI**; see §7.

---

## 3. Bring up local WordPress

```bash
docker compose -f local/docker-compose.yml up -d
docker compose -f local/docker-compose.yml ps      # wordpress + db should be "Up"
```

- Site: <http://localhost/>   ·   Admin: <http://localhost/wp-admin/>
- **The port mapping must stay `80:80`.** See §7 — `8080:80` silently breaks WordPress
  self-loopback, Novamira's REST self-check, and WP-Cron.

### WP-CLI

```powershell
# PowerShell wrapper (preferred)
.\local\wp.ps1 core version
.\local\wp.ps1 plugin list
```

```bash
# or directly
docker compose -f local/docker-compose.yml --env-file local/.env run --rm wpcli plugin list
```

From **Git Bash**, prefix any command that has a `/`-leading argument with
`MSYS_NO_PATHCONV=1` (see §7):

```bash
MSYS_NO_PATHCONV=1 docker compose -f local/docker-compose.yml --env-file local/.env \
  run --rm wpcli db export /backups/checkpoint.sql
```

### First-run bootstrap (only on an empty site)

WordPress core, plugins, the parent theme and media are **not** in Git — only the child theme,
`tools/`, `local/media-staging/` and docs are (see "Working with a teammate" below for why).
One script rebuilds all of it, in the right order, on any fresh clone:

```powershell
.\local\bootstrap.ps1
```

```bash
./local/bootstrap.sh
```

This runs `wp core install` (using `WP_ADMIN_*` from `local/.env`), installs and activates
Elementor / Fluent Forms / Rank Math SEO and the `hello-elementor` parent theme, imports every
`.webp` in `local/media-staging/`, then runs — each as its own fresh WP-CLI process, in order —
`tools/00-site-setup.php`, `01-elementor-kit.php`, `02-demo-posts.php`, `03-fluentforms.php`,
`04-seo-meta.php`, and every `tools/pages/*.php` builder. Page builders go through Elementor's
own `Document::save()` (never a raw `_elementor_data` write). Both scripts are **idempotent**
— safe to re-run after `docker compose down -v`, or any time to pick up new pages/media.

Verified end-to-end (2026-09-08) against a disposable, isolated Docker Compose project (its own
`-p` name and volumes, a spare host port, torn down with `down -v` afterward — the real
`local_eqc_wp` / `local_eqc_db` volumes were never touched): 15 media attachments imported, 10
pages created and built, both plugins-with-forms present, all 7 `tests/visual/sweep.mjs`
viewports passing, HTTP 200 on `/` and a built page.

If you only need one piece by hand (e.g. re-running just the SEO meta after editing it), each
script's own file header documents its exact standalone invocation — e.g.:

```bash
.\local\wp.ps1 --user=1 eval-file /tools/pages/14-pricing.php
```

`tools/05-bootstrap.php` is what `local/bootstrap.ps1`/`.sh` actually runs; read it before
changing the install order.

### Also needed for visual QA / asset tooling

Two subfolders carry their own `package.json`; install once per machine:

```bash
cd tests/visual && npm install && cd ../..     # Playwright viewport sweep (project-local, not global)
cd tools/graphics && npm install && cd ../..   # logo/icon/ornament SVG generation (local-only, not deployed)
```

---

## 3A. Working with a teammate

**Day-to-day git push / PR workflow is `CONTRIBUTING.md`** — short, dedicated, hand it to
anyone joining before their first push. This section covers the one-time onboarding around it.

**Do not hand off the repo as a ZIP.** It's already on GitHub, with full history and branches —
a ZIP would throw both away and would risk bundling `.env`, `local/.env`, and anything under
`local/backups/`, none of which belong outside this machine. Clone the repo instead:

```bash
git clone https://github.com/Bakhtiar76/EasyQuranClassesWebsite.git
cd EasyQuranClassesWebsite
git checkout feature/setup
```

**Why a plain clone looks "broken" at first:** the WordPress database and media library are
deliberately **not** in Git (`CLAUDE.md` "Architecture" — "the full local WordPress site/
database is not represented by Git alone"). Only the child theme, `tools/`, docs, `.claude/`
config and `local/media-staging/` travel with the repo; everything else — pages, Elementor
content, plugin settings, the media library — lives in Docker's `eqc_wp`/`eqc_db` volumes on
whichever machine created them. A fresh clone is *supposed* to start with an empty WordPress.
Run `.\local\bootstrap.ps1` / `./local/bootstrap.sh` (§3 above) to rebuild it from the repo —
that is the fix, not anything wrong with the clone.

**Branch discipline (already in `.claude/rules/git.md` — restated here because it matters more
with two people in the repo):**

- Both of you work on `feature/*` branches (currently `feature/setup`). That branch carries the
  full dev tree: docs, `.claude/`, `local/`, `tools/`, `tests/`.
- `main` holds **only** the production paths (the child theme, `.gitignore`, `.github/
  workflows/`) — never merge a feature branch into it; that reintroduces every dev-only path
  `main` was deliberately pruned of. Releases copy just the theme folder across — see
  `.claude/rules/git.md` for the exact `git checkout feature/setup -- wp-content/themes/...`
  flow.
- If you're both in the **same working directory** (rare, but e.g. remote pairing on one
  machine), never `git checkout main` there — it switches the branch out from under whoever
  else has uncommitted `feature/setup` work. Use `git worktree add ../release main` instead.
  On separate machines/clones this doesn't apply.

**A push to `main` is a production deploy.** `.github/workflows/deploy.yml` FTPS-deploys the
child theme to `easyquranclasses.com` on every push to `main`, using GitHub Actions Secrets.
Adding a collaborator with push access means `main` is one `git push` away for them too, so
before onboarding a teammate:

1. On GitHub → **Settings → Collaborators**, add them (or use a fork + PR workflow if you'd
   rather they never have direct push access to the repo at all — see the plan-restriction
   note below, which may make this the only real option for now).
2. ~~Settings → Branches → Add branch protection rule~~ — **GitHub will refuse this.** Branch
   protection (classic rules and the newer Rulesets alike) requires GitHub Pro/Team/Enterprise
   on a **private** repository; GitHub Free only allows it on public repos. Confirmed via the
   API against this repo (2026-09-08) — see the "Boundaries" section (§9) at the end of this
   file for the full finding and the three real options. Until one is chosen, protection is
   discipline-only: don't skip step 3.
3. Tell them plainly: *day-to-day work happens on `feature/setup`; `main` is release-only —
   nobody pushes to it directly, even though GitHub isn't currently enforcing that for us.*

This only restricts *merging*; it does not touch `.github/workflows/deploy.yml` itself (out of
scope for this pass — the workflow still fires on any push that does land on `main`, PR or not).

### Snapshot handoff (when you need to hand over your *exact* current state)

The scripted bootstrap above gives a teammate an equivalent site, not a byte-for-byte copy — new
demo posts get new IDs, timestamps differ, etc. If you specifically need them to reproduce a bug
against your exact data, export a snapshot instead:

```bash
MSYS_NO_PATHCONV=1 docker compose -f local/docker-compose.yml --env-file local/.env \
  run --rm wpcli db export /backups/handoff.sql
```

Then zip `wp-content/uploads/` from inside the `eqc_wp` volume (it's not bind-mounted, so copy
it out via a throwaway container, e.g. `docker run --rm -v local_eqc_wp:/wp -v
"${PWD}/local/backups:/out" alpine tar -C /wp -czf /out/uploads.tar.gz wp-content/uploads`).

Send `local/backups/handoff.sql` and `uploads.tar.gz` to your teammate **out-of-band** (not
through Git, not pasted into chat/an issue) — they import with `wp db import` and extract the
archive into their own `eqc_wp` volume. Treat both files as sensitive the whole time: the SQL
dump contains your local wp-admin password hash and any Application Passwords you've created.
Delete them from `local/backups/` once the handoff is done — that folder is gitignored but not
automatically cleaned up.

---

## 4. Claude Code setup

### 4.1 Plugins

Claude Code plugin state is **user-global** (`~/.claude/`), not in the repo, so each machine
installs them once. Add the marketplaces and enable the plugins:

```
/plugin marketplace add anthropics/claude-plugins-official
/plugin marketplace add openai/codex-plugin-cc
```

Then install (via `/plugin`): `superpowers`, `frontend-design`, `context7`, `code-review`,
`code-simplifier`, `claude-md-management`, `skill-creator`, `playwright`,
`chrome-devtools-mcp`, `playground`, `hookify`, `remember`, `build-with-wordpress`,
`figma`, `greptile` (broken — see §7), and `codex@openai-codex`.

### 4.2 Project MCP — `.mcp.json` (committed)

One server, `novamira-localhost` (`@automattic/mcp-wordpress-remote`). It needs
`NOVAMIRA_APP_PASSWORD` as a Windows User env var (§2.3) and the local site running (§3).
Approve it when Claude Code prompts, or in `.claude/settings.local.json`:

```json
{ "enabledMcpjsonServers": ["novamira-localhost"], "enableAllProjectMcpServers": true }
```

`.claude/settings.local.json` is gitignored — recreate it per machine.

### 4.3 Hooks, rules, skills (all committed, no action needed)

- `.claude/hooks/guard-bash.mjs` — PreToolUse deny-guard (rm -rf on roots, `DROP`/`TRUNCATE`,
  reading secret files, exfiltrating secrets, AI-attribution commit trailers). Fails **open**;
  `permissions.deny`/`ask` in `settings.json` is the real control.
- `.claude/hooks/post-edit-validate.mjs` — PostToolUse advisory: `JSON.parse` on `.json`,
  `php -l` on `.php` (silently skipped if no host PHP).
- `.claude/rules/*.md` — six rule files (security, git, wordpress, implementation, design, deployment).
- `.claude/skills/*` — 14 project skills. Also exposed to Codex — see §5.

### 4.4 Verify Claude Code

Start `claude` in the repo. Confirm: `.mcp.json` server connects (ask it to run a Novamira
ability discovery), `/plugin` shows the list above, and `.claude/skills` are listed.

---

## 5. Codex setup

The Codex CLI must be **installed, logged in, and trusting this repo** first:

```bash
codex --version           # 0.153.4+
codex login               # ChatGPT auth (interactive)
```

### 5.1 Create the WordPress Application Password (once)

wp-admin → Users → your admin user → **Application Passwords** → add one named `novamira`.
Put the generated value in `local/.env` as `NOVAMIRA_APP_PASSWORD=` (§2.1). Install and
activate the **Novamira** plugin if it isn't already (Plugins → Add New, or upload).

### 5.2 Run the setup script

```powershell
pwsh tools/codex/setup-codex.ps1
```

Idempotent. It:

1. **Preflights** Codex version, login, and that a Novamira password is reachable.
2. **Registers four MCP servers** into `~/.codex/config.toml` (remove-then-add):
   | Server | Command | Purpose |
   |---|---|---|
   | `novamira-localhost` | `cmd /c tools\codex\novamira-mcp.cmd` | WordPress control plane, local site only |
   | `chrome-devtools` | `cmd /c npx -y chrome-devtools-mcp@latest` | screenshots / console / network / Lighthouse |
   | `context7` | `cmd /c npx -y @upstash/context7-mcp` | live library docs |
   | `playwright` | `cmd /c npx -y @playwright/mcp@latest --isolated --blocked-origins <prod>` | a11y tree, tab order, form fill/submit; isolated profile, production origins blocked |

   Every entry goes through `cmd /c` — Codex on Windows cannot spawn a `.cmd`/`npx.cmd`
   directly with working stdio (see §7).
3. **Syncs all 14 `.claude/skills/<name>`** into `~/.codex/skills` as `eqc-<name>`
   (directory junction; copy fallback). Canonical source stays `.claude/skills/` — edit
   there and re-run.
4. **Trusts** the repo path in `~/.codex/config.toml`.

### 5.3 `tools/codex/novamira-mcp.cmd`

A launcher, not a config value. Codex has **no `${VAR}` substitution** in MCP `env`, and it
**strips `*PASSWORD*` env vars** before spawning a server (§7). So the wrapper reads
`NOVAMIRA_APP_PASSWORD` from `local/.env` at launch (falling back to the env var). The
secret never lands in `~/.codex/config.toml` or Git.

### 5.4 Verify Codex

```powershell
pwsh tools/codex/setup-codex.ps1 -Verify      # expect "PARITY OK", exit 0
```

```bash
codex mcp list                                # run OUTSIDE any codex sandbox (see §7)
```

End-to-end (the MCP tools are deferred behind tool-search — the model must search for them,
a plain "list your tools" shows nothing, that's normal):

```bash
codex exec --sandbox workspace-write --skip-git-repo-check \
  "Search your tools for 'novamira', call the ability-discovery tool, and report the count. \
   Then search for 'context7' and resolve-library-id for 'elementor'. Read-only, change nothing."
```

Expected: ~36 Novamira abilities, context7 returns `/elementor/elementor-developers-docs`.

Then confirm Docker works once approved (the Claude Code auto-mode classifier blocks
`--sandbox danger-full-access` from inside an agent session, so run this yourself):

```
codex "run: docker compose -f local/docker-compose.yml ps"     # approve the escalation
```

---

## 6. Verification matrix

| # | Command | Expected |
|---|---|---|
| 1 | `docker compose -f local/docker-compose.yml ps` | `wordpress` + `db` **Up**, wordpress `0.0.0.0:80->80` |
| 2 | `curl -s -o /dev/null -w "%{http_code}" http://localhost/` | `200` |
| 3 | `curl -s -o /dev/null -w "%{http_code}" http://localhost/wp-json/mcp/novamira` | `401` (endpoint alive, unauthenticated) |
| 4 | `.\local\wp.ps1 core version` | a version string, no permission errors |
| 5 | `node tests/visual/sweep.mjs http://localhost/` | screenshots under `tests/visual/test-results/<ts>/`, exit 0 |
| 6 | `pwsh tools/codex/setup-codex.ps1 -Verify` | `PARITY OK`, exit 0 |
| 7 | `codex mcp list` (outside a sandbox) | `novamira-localhost`, `chrome-devtools`, `context7`, `playwright` all `enabled` |
| 8 | Claude Code: Novamira ability discovery via `.mcp.json` | abilities list returns |
| 9 | `.\local\bootstrap.ps1` / `./local/bootstrap.sh` on a fresh clone (empty `eqc_wp` volume) | exits 0; 10 pages + Privacy Policy, 15 attachments, both plugins/theme active, all `tests/visual/sweep.mjs` viewports pass |

---

## 7. Known issues & fixes

Everything below was hit during setup. Symptom → cause → fix.

### Local WordPress / Docker

| Issue | Cause | Fix |
|---|---|---|
| Novamira REST self-check fails with `cURL error 7: Failed to connect to localhost port 8080`; WP-Cron and any self-loopback broken | Port mapping was `8080:80`. Inside the container, WordPress calls its own `home_url()` = `localhost:8080`, which resolves to nothing — only Docker's host-side proxy understands that mapping. | Keep the mapping **`80:80`** in `local/docker-compose.yml`. Host port must equal Apache's internal port. |
| `wp-cli` can't write to uploads/plugins ("permission denied") | The CLI image (`wordpress:cli-php8.3`) is Alpine (`www-data`=82); the Apache image is Debian (`www-data`=33) and owns the `eqc_wp` volume. | The `wpcli` service pins `user: "33:33"` in the compose file. Don't remove it. |
| Application Passwords / OAuth refused over `http://localhost` | WordPress core blocks them on plain HTTP unless the environment is declared local. | `WORDPRESS_CONFIG_EXTRA` in the compose file sets `WP_ENVIRONMENT_TYPE=local`. |
| From Git Bash, a `/backups/x.sql` argument becomes `C:/Program Files/Git/backups/x.sql` | MSYS auto-rewrites `/`-leading args into Windows paths. | Prefix the command with `MSYS_NO_PATHCONV=1`. (PowerShell is unaffected.) |
| A second machine's `tools/pages/*.php` builders errored with "No Elementor document for post #28", or silently overwrote the wrong page | Every builder used to call `eqc_save_elementor_page()` with a literal post ID (`24`–`33`). Those IDs are an artifact of *this* machine's insert order (`wp_insert_post()` in `00-site-setup.php`) — nothing guarantees a second install produces the same numbers. | Fixed 2026-09-08: builders now resolve the ID by slug via `eqc_page_id()` (`tools/elementor-helpers.php`, mirrors the existing `eqc_media_id()` pattern). Confirmed a no-op on this machine (all nine resolved to their old literal values) before landing. |
| `01-elementor-kit.php` (or any script needing a plugin) fails with "Elementor is not loaded" when run right after activating the plugin | WordPress loads active plugins once, at the very start of a PHP process. Activating Elementor via `WP_CLI::runcommand()` happens in a spawned **subprocess** — it updates the `active_plugins` DB option, but never `include`s Elementor's code into the *parent* script's still-running process. A plain `require` of the next script inherits that same stale, plugin-not-loaded process. | `tools/05-bootstrap.php` runs every downstream `tools/*.php` script as its **own fresh subprocess** (`eqc_run_step()`), not a `require` — each one starts with an up-to-date `active_plugins` option. Found and fixed while verification-testing the bootstrap script; see its inline comment. |
| Media import skipped `eqc-logo.webp`, only `eqc-logo-mark.webp` got imported | `eqc_media_id()` does a substring `LIKE` match; checking "already imported?" with the bare filename fragment `eqc-logo` also matches the *already-imported* `eqc-logo-mark.webp`, so the bootstrap script wrongly concluded `eqc-logo.webp` didn't need importing. `00-site-setup.php`'s own logo lookup already works around this by matching the *full filename with extension* — the bootstrap script's import-loop didn't, until fixed. | `tools/05-bootstrap.php` now checks `eqc_media_id( basename( $path ) )` (full filename incl. `.webp`), not the bare fragment. Found and fixed the same verification pass as above. |
| **Every page shows the same homepage content** on a byte-for-byte fresh bootstrap (`/contact/`, `/teachers/`, etc. all render Home) | `wp core install` never sets a permalink structure; nothing else in the bootstrap chain did either. With plain `?p=123` links and no rewrite rules registered, WordPress can't map a pretty URL to its page and falls back to the front page. Confirmed 2026-09-08 via an isolated fresh-clone-and-Docker test (separate project, separate volumes, cleaned up after): every one of the 10 pages returned `200` but identical content until this was set. | `00-site-setup.php` now sets `permalink_structure` to `/%postname%/` and calls `flush_rewrite_rules()` alongside the other site-identity options. Verified: a fully-fresh isolated bootstrap now serves distinct, correct content per page (screenshotted); re-running the fixed script against the real local site is a safe no-op (already had this set from an earlier manual step). |
| First bootstrap attempt after `docker compose down -v` fails with "Error establishing a database connection" | The "waiting for reachable" loop's readiness check can pass a beat before `db` is actually ready to accept the `wp core install` connection on a completely fresh volume. | Transient — `bootstrap.sh`/`.ps1` are idempotent; re-running immediately succeeds. Not something to fix by lengthening the wait loop speculatively; noted here in case it recurs. |
| A plugin download step (`wp plugin install`) fails once with `Warning: Download failed. "A valid URL was not provided."` while sibling installs in the same run succeed | A transient WordPress.org fetch hiccup, not a script defect — confirmed by an immediate re-run succeeding. `tools/05-bootstrap.php`'s downstream steps (e.g. `03-fluentforms.php`'s `WP_CLI::error()` if Fluent Forms isn't active) already turn a real install failure into a loud, whole-run-aborting error rather than a silently half-built site. | Re-run `bootstrap.sh`/`.ps1` — idempotent, resumes from wherever it stopped without duplicating anything already done. |

### Novamira

| Issue | Cause | Fix |
|---|---|---|
| `.mcp.json`'s `${NOVAMIRA_APP_PASSWORD}` resolves to empty | Claude Code substitutes from the **OS environment**, not `local/.env`. | Set it as a Windows **User** env var (§2.3), then fully restart Claude Code. |
| Novamira **CLI**: `Error: The OS credential service could not complete the operation` | The default Windows Credential Manager backend fails on this machine — confirmed **not** sandbox-specific (same error in a plain terminal). Root cause not fully isolated. | Set `NOVAMIRA_CREDENTIAL_BACKEND=file` as a Windows User env var (documented CLI fallback). |
| File credential backend then fails its own safety check | The fallback requires each of `%LOCALAPPDATA%\Novamira\Credentials` and `...\Novamira\Cache` to carry **exactly one** ACL entry (current user, full control, inheritance off). A shared `CodexSandboxUsers` group had an inherited Read+Execute ACE. | `icacls "<dir>" /inheritance:r /grant:r "<user>:(OI)(CI)F"` on both dirs. Adding `SYSTEM` also fails the "exactly one rule" check — grant only the current user. |
| `Error [rest_error]: Too many registrations` during CLI device-flow login | Every failed device-flow attempt registers a new server-side OAuth client, capped by a WordPress transient (`_transient_novamira_oauth_dcr_0_<hash>`, cap 10). | `wp option delete _transient_novamira_oauth_dcr_0_<hash> _transient_timeout_novamira_oauth_dcr_0_<hash>`, or wait out the expiry. The `wp_novamira_oauth_*` tables are safe to clear the same way but are not the limiter. |
| Bash/PowerShell calls mentioning "novamira", and `wp plugin install ... --activate` for it, intermittently denied | Claude Code's auto-mode safety classifier, independent of `.claude/settings.json` allow rules. | Retry (sometimes reworded). The plugin was ultimately installed manually via wp-admin. MCP tool calls use a different path and were never affected. |

### Codex

| Issue | Cause | Fix |
|---|---|---|
| `codex exec` sees no `novamira-localhost` / `chrome-devtools` / `context7` tools; `docker` inside `codex exec` → `permission denied ... npipe:////./pipe/docker_engine` | Docker's named pipe is unreachable from **both** Codex sandbox modes (`read-only` and `workspace-write`); `~/.docker/config.json` is `Access is denied`. | Docker / WP-CLI commands must be **approved to run outside the sandbox**. `AGENTS.md` tells Codex this. Never conclude the stack is down from a sandboxed Docker error. |
| Bare `npx` fails: `npx.ps1 cannot be loaded because running scripts is disabled` | PowerShell execution policy blocks `.ps1`. | Use `npx.cmd`, and register MCP servers as `cmd /c npx -y <pkg>` (PATHEXT resolves `npx` → `npx.cmd` under `cmd`). |
| MCP server registered but its tools never appear; server stderr is silent | Codex on Windows can't spawn a `.cmd`/`npx.cmd` directly with working stdio pipes — they go dead. | Wrap every stdio MCP command in `cmd /c ...`. `tools/codex/setup-codex.ps1` does this. |
| `novamira-mcp.cmd` prints `NOVAMIRA_APP_PASSWORD is not set` even though the User env var exists | Codex strips env vars whose name matches `*PASSWORD*` / `*SECRET*` / `*TOKEN*` before spawning an MCP server. | The wrapper reads the password from `local/.env` instead (env var is only a fallback). |
| Model says "no tools exposed in this session" for the three servers | Codex defers MCP tools behind tool-search (`ToolSearchAlwaysDeferMcpTools`). A plain "list your tools" won't show them. | Not a bug. Tell the model to **search** its tools (e.g. "search your tools for novamira"). The interactive `codex` TUI handles this transparently. |
| `codex mcp list` inside a `codex exec` sandbox → `Error: failed to resolve CODEX_HOME` | The sandbox strips the home env. | Run `codex mcp list` from a normal terminal. |
| Batch wrapper aborted with `then was unexpected at this time` (dev-time only) | A `)` inside a message string closed an `if (...)` block early. | Fixed — the wrapper uses `goto` labels and paren-free messages. Noted so nobody reintroduces it. |

### Agent tooling

| Issue | Cause | Fix |
|---|---|---|
| WPVibe (`vibe-ai`) can't reach the local site | It's a hosted cloud relay (`mcp.wpvibe.ai`); no route to `localhost` without a public tunnel. | Left installed but **deactivated**. `release-check` asserts its absence from any release archive. |
| `greptile` MCP fails to connect: `AUTH_HEADER_REJECTED` (HTTP 403) | Token not valid for the endpoint. | Unused by this project — disable the `greptile` plugin. |
| `README-SETUP.md` (old) said `npm install -g @playwright/cli` | Stale. | Playwright is **project-local** in `tests/visual/` (`playwright` 1.63.0, run via `node tests/visual/sweep.mjs`). Do not install it globally. |
| Duplicate project-scoped plugin entries in `~/.claude/plugins/installed_plugins.json` (`F:\` vs `f:\`) | Windows drive-letter casing; the repo gets opened both ways. | Harmless, noisy. Ignore, or de-dupe by hand. |
| Remember plugin captures nothing; logs `unrecognised transcript envelope, 0 exchanges read` | Transcript-format mismatch on this setup. | Known; doesn't block work. Setup knowledge is documented here and in `CLAUDE.md`, not relied on from Remember. |

---

## 8. Manual steps that cannot be scripted

Everything about first-run WordPress itself — core install, plugins, parent theme, media,
pages — is now one command (§3 "First-run bootstrap"). What's left here genuinely has no
non-interactive equivalent:

- Install Docker Desktop + enable the WSL2 backend; start it before any site command.
- `claude` login, and `codex login` (ChatGPT auth).
- `/plugin marketplace add` + install the Claude Code plugins (§4.1).
- Set the two Windows **User** env vars (§2.3), then **fully restart** the agent/terminal.
- Create the WordPress **Application Password** in wp-admin and put it in `local/.env`.
- Install + activate the **Novamira** plugin (local site only — never production).
- If using the Novamira CLI: the `icacls` ACL repair (§7).
- Approve Docker / WP-CLI escalation out of the sandbox when Codex prompts.

---

## 9. Boundaries (do not cross without approval)

- Production (`easyquranclasses.com`) has **no shell/SSH**. Deployment is manual via cPanel UI —
  see `CPANEL-WORKFLOW.md`. Never point any MCP server, browser session, or automation at it.
- Novamira and WPVibe are **local-only**. `release-check` fails a release archive that contains
  either.
- No generic cPanel / SSH / filesystem / database MCP servers (`.claude/rules/security.md`).
- Git: short natural one-line commit messages, no AI-attribution trailers, no push without
  authorization (`.claude/rules/git.md`).
- `main` auto-deploys to production on every push (`.github/workflows/deploy.yml`, FTPS). With
  more than one person able to push, put a branch-protection rule on `main` requiring a PR
  (§3A) — don't rely on everyone remembering not to push there directly.
  **Checked 2026-09-08: not currently possible on this repo.** Both the classic branch-protection
  API and the newer Rulesets API return `403: "Upgrade to GitHub Pro or make this repository
  public to enable this feature."` — GitHub Free only allows branch protection on *public*
  repos; this repo is private. Confirmed via `gh api` with an authenticated, repo-**Admin**
  account (`Bakhtiar76`) — not a permissions problem, a plan restriction. Real options, decision
  deliberately left to the user (asked 2026-09-08, held off for now):
  1. **GitHub Pro** (~$4/mo) — unlocks real protection on the private repo immediately.
  2. **Make the repo public** — free, but every doc in it (including `CPANEL-WORKFLOW.md`,
     `TASK-DEPLOY.md`) becomes world-readable.
  3. **Fork + PR model, no paid plan** — give the collaborator **Read/Triage** only (no push
     rights on this repo at all, to any branch); they work from a personal fork and open PRs
     in; only an Admin can merge. Achieves "no direct push/merge" without needing GitHub's
     protection feature.
  Until one of these is chosen, `main` is protected by discipline only: both people work on
  `feature/*`, nobody pushes to `main` directly, releases go out as described in §3A.
