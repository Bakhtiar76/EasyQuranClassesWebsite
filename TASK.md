# TASK — Professional Claude Code Setup for Easy Quran Classes

## Purpose

Set up this repository so Claude Code can work on the Easy Quran Classes WordPress project safely, efficiently, and predictably across local development, staging, WordPress, Git/GitHub, browser QA, and cPanel/SSH workflows.

This is a **development-environment setup task**, not a website-build task.

Do not begin page implementation, Elementor page building, content migration, production deployment, or cPanel configuration changes while completing this task.

The setup must be minimal, auditable, and appropriate for a client WordPress production environment.

---

# 1. Read these files first

Before doing anything else, read and follow:

1. `CLAUDE.md`
2. `DESIGN.md`
3. `CPANEL-WORKFLOW.md`
4. `.gitignore`
5. all files in `.claude/rules/`
6. all existing `.claude/skills/*/SKILL.md`
7. this `TASK.md`

If instructions conflict, apply the safer rule and report the conflict before changing anything.

---

# 2. Non-negotiable implementation rules

These rules apply to **this setup task and every future implementation task**.

For every task, create a TODO list before making changes. The first items in every implementation TODO list must cover these rules.

## Required TODO template

Every implementation task must begin with at least:

- [ ] Scan the repository and relevant WordPress/project state before implementation.
- [ ] Identify existing code, styles, helpers, libraries, hooks, templates, skills, or configuration that can be reused.
- [ ] If nothing suitable can be reused, state briefly why a new implementation is necessary.
- [ ] Define the smallest correct implementation and avoid unnecessary abstraction.
- [ ] Check whether an already installed/native library or WordPress/Elementor feature can solve the requirement with less custom code.
- [ ] Identify the correct existing file/location for each piece of logic before creating anything new.
- [ ] Define expected edge cases and verification before writing code.
- [ ] Implement only the minimal required change.
- [ ] Test every new or modified piece of code, including relevant edge cases.
- [ ] Remove dead, obsolete, duplicated, or unused code introduced or exposed by the change when it is safe and within scope.
- [ ] Update relevant documentation and project checklists.
- [ ] Keep `CLAUDE.md` current with durable project knowledge only.
- [ ] Review Git status/diff and confirm no secrets, generated files, or unrelated changes are included.
- [ ] Run applicable verification and report anything that still requires manual checking.

## Reuse-first rule

Before implementing anything, scan the repository and relevant project state to see whether an existing code block, WordPress feature, Elementor feature, helper, style, template, library, plugin, hook, or utility can be reused.

Reuse existing suitable implementation instead of duplicating it.

If a new implementation is required, state the reason briefly before creating it.

## Minimal-code rule

For every feature or fix:

1. Find the simplest correct solution.
2. Prefer native WordPress, Elementor Free, browser APIs, or an already installed dependency.
3. Prefer a small extension of existing code over creating a parallel system.
4. Do not add a library when a small, clear implementation is safer and simpler.
5. Do not write custom code when a reliable existing project dependency already solves the requirement cleanly.
6. Do not create abstractions for hypothetical future requirements.

The target is **minimal maintainable code**, not minimum line count at the cost of clarity.

## Correct file ownership

Every piece of logic must live where it belongs.

Examples:

- presentation/theme behavior → child theme
- business functionality that should survive theme changes → site-specific plugin
- Elementor composition/content → Elementor
- reusable design rules → `DESIGN.md` / child-theme CSS
- deployment procedures → deployment docs/skills
- Claude behavior → `CLAUDE.md`, `.claude/rules/`, `.claude/skills/`, hooks/settings

Do not create empty, placeholder, duplicate, or unnecessary directories/files.

Before creating a new file, verify an appropriate existing file does not already exist.

## Dead-code rule

After implementation, remove dead or unused code created by or made obsolete by the task.

If existing dead/unused code is encountered and it directly interferes with the current implementation, either:

1. safely reuse/fix it, or
2. remove it when removal is clearly within scope and tested.

Do not perform unrelated cleanup across the entire repository merely because unused code exists elsewhere.

Never leave temporary implementations, commented-out replacements, debug statements, duplicate functions, stale imports, or abandoned files pending after a completed iteration.

## Edge-case testing rule

Every code change must be tested against realistic edge cases relevant to that change.

Examples include:

- empty/missing content
- malformed input
- unauthorized users
- nonce failure
- network or form failure
- unavailable dependency
- responsive overflow
- long text
- missing images
- duplicate actions
- invalid URLs
- PHP warnings/notices
- JavaScript console errors

Only test relevant edge cases; do not create meaningless tests simply to satisfy a checklist.

---

# 3. Documentation discipline

Documentation must describe the actual project, not aspirational or stale state.

After each meaningful iteration:

1. update any task/source checklist whose items were completed;
2. update documentation affected by the implementation;
3. update `CLAUDE.md` when durable architecture, commands, dependencies, workflows, constraints, or project state have changed;
4. do not copy transient task logs into `CLAUDE.md`;
5. keep `CLAUDE.md` concise and **never allow it to exceed 1000 lines**;
6. prefer `.claude/rules/`, `.claude/skills/`, or dedicated docs for detailed procedures that do not need to load into every Claude session.

`CLAUDE.md` must always contain enough current knowledge for a new Claude session to understand the active architecture and constraints without rediscovering basic decisions.

Do not rewrite source/reference documents solely to mirror implementation status unless the document contains an explicit checklist/status section.

---

# 4. Git / GitHub workflow — mandatory

Git must be used as a disciplined development checkpoint system for project code/configuration.

First inspect the existing repository, remotes, branches, `.gitignore`, and any existing contribution or branch rules. Do not overwrite an established safe workflow.

If no workflow exists, use the following default.

## Branch rules

- `main` represents production-ready code.
- Do not make experimental work directly on `main` when a feature/fix branch is appropriate.
- Use concise branch names such as:
  - `feature/homepage-foundation`
  - `feature/header-footer`
  - `fix/mobile-header`
  - `chore/claude-setup`
- Do not create a branch for a trivial one-file documentation correction if the existing repository workflow does not require it.
- Never force-push unless the user explicitly approves it after the risk is explained.
- Never rewrite shared history casually.

## Before every commit

Run/review the applicable equivalent of:

```bash
git status
git diff --check
git diff
```

Then:

1. ensure only intended files changed;
2. ensure there are no credentials, `.env` files, private keys, DB dumps, backups, logs, generated caches, or unrelated files;
3. run relevant tests/checks;
4. stage only the intended files;
5. review the staged diff before committing.

Prefer explicit staging:

```bash
git add path/to/file path/to/other-file
```

Do not use `git add .` blindly.

## Commit message rules

Every commit message must be:

- clean
- clear
- concise
- brief
- human-readable
- specific to one coherent change

Prefer a short conventional form where useful:

```text
feat: add responsive course cards
fix: correct mobile header spacing
chore: add project safety rules
docs: update deployment workflow
refactor: reuse shared button styles
```

Rules:

- keep the subject preferably under 72 characters;
- use one-line commit messages for ordinary changes;
- use imperative/action-oriented wording;
- avoid vague messages such as `updates`, `changes`, `work`, `fix stuff`;
- do not write long AI-style summaries in commit messages;
- do not mention Claude, AI, generated code, prompting, or model usage in normal implementation commit messages;
- do not add `Generated by Claude`, `Generated with AI`, `Co-Authored-By: Claude`, or similar AI attribution trailers;
- do not fabricate ticket numbers;
- only include a commit body when a genuinely important technical reason or migration note cannot fit in the subject.

## Commit boundaries

Create commits at coherent checkpoints, not after every tiny edit and not as one huge mixed commit.

A commit should normally represent one of:

- one feature milestone;
- one bug fix;
- one setup/configuration improvement;
- one focused refactor;
- one documentation update tied to a completed milestone.

Do not combine unrelated work into the same commit.

## GitHub-specific rules

If the repository uses GitHub:

1. inspect the remote and existing branch/PR workflow first;
2. use GitHub only when it materially improves collaboration, PRs, issues, or review;
3. do not assume a GitHub plugin/MCP is required just to make local commits;
4. do not push unless the task/user explicitly authorizes pushing;
5. do not merge a PR, change branch protection, delete remote branches, create releases, or change repository settings without explicit approval;
6. never expose GitHub tokens or credentials in project files/logs.

If an official GitHub Claude Code integration is useful, install it only after confirming the repo is actually hosted on GitHub and the required access scope is appropriate.

---

# 5. Setup goal

After this task, the repository should have a professional Claude Code configuration that provides:

- durable project instructions;
- modular rules;
- reusable WordPress/Elementor/cPanel/QA skills;
- safe permissions;
- safety hooks;
- local development tooling where justified;
- browser QA tooling;
- Git/GitHub workflow controls;
- WordPress/WP-CLI workflow;
- cPanel/SSH workflow without unnecessary privileged MCP access;
- a documented inventory of installed Claude plugins/MCP servers/tools;
- a tested setup.

Do not install tools merely because they exist.

---

# 6. Phase A — audit before changing anything

Create the required TODO list first.

Then audit the repository and machine.

## Repository audit

Inspect:

- repository root;
- current branch;
- Git status;
- remotes;
- existing `.claude/` configuration;
- `CLAUDE.md`;
- `DESIGN.md`;
- current skills/rules/hooks/settings;
- package files if present;
- WordPress files if present;
- child theme/plugin structure if present;
- existing test/lint/build tools;
- existing docs.

Do not modify anything during this first audit step.

## Toolchain audit

Report availability/versions of relevant tools, without installing anything yet:

```text
Claude Code
Git
GitHub CLI (gh), if present
Node.js
npm
PHP
Composer
WP-CLI
SSH
rsync
jq on Bash/WSL/Linux
PowerShell on Windows
Chrome/Chromium
Playwright CLI
Lighthouse
```

Determine whether Claude is running in:

- native Windows;
- WSL;
- Linux;
- macOS.

If Windows and WSL are both available, recommend one primary development shell based on the actual installed toolchain and project path. Avoid maintaining duplicate toolchains unless necessary.

## Security audit

Check whether the repository location contains or can reach unrelated sensitive files.

Do not read secret values.

Identify risks such as:

- project inside a broad personal/home directory;
- project inside OneDrive/Dropbox with unwanted sync behavior;
- tracked `.env` or `wp-config.php`;
- DB dumps/backups;
- SSH keys inside the project;
- overly broad existing MCP servers;
- existing Claude allow rules that grant unnecessary shell/network access.

Report findings before setup changes.

---

# 7. Phase B — normalize Claude project instructions

Review existing `CLAUDE.md` and `.claude/rules/` before creating anything new.

Do not duplicate the same detailed instructions across multiple files.

Required durable rule areas:

```text
.claude/rules/security.md
.claude/rules/wordpress.md
.claude/rules/design.md
.claude/rules/deployment.md
.claude/rules/implementation.md
.claude/rules/git.md
```

If equivalent files already exist, update/reuse them instead of creating duplicates.

`implementation.md` must contain the reuse-first, minimal-code, TODO, testing, correct-file, dead-code, and documentation rules from this task.

`git.md` must contain the Git/GitHub and concise commit rules from this task.

Keep `CLAUDE.md` as the concise project-level source of truth and reference the detailed rules rather than copying every detail into it.

Do not exceed 1000 lines in `CLAUDE.md`.

---

# 8. Phase C — configure Claude permissions safely

Inspect the currently installed Claude Code version and current official settings schema/documentation before writing `.claude/settings.json`.

Do not rely on obsolete syntax.

Create/update a **shared project** `.claude/settings.json` only after validating its structure.

Keep machine-specific approvals in `.claude/settings.local.json`, which must remain untracked.

## Permission goals

### Deny

Block access to clearly sensitive project files where practical, including patterns for:

- `.env`
- `.env.*`
- private keys
- credential files
- DB dumps
- backup archives
- unrelated SSH-key material

Do not configure a broad deny that prevents legitimate WordPress tooling from running unless there is a safer alternative.

### Ask

Require explicit permission for high-impact actions including applicable patterns for:

- `git push`
- force push
- destructive Git history changes
- SSH remote commands when appropriate
- production deployment
- `wp search-replace`
- WordPress plugin/theme deletion
- DB import/reset/drop
- broad `rsync`
- broad file deletion
- network/tool installation commands that materially change the machine

### Allow

Allow only clearly safe, recurring project commands after inspecting the actual toolchain, such as selected read-only commands/tests.

Do not create an enormous permissive allowlist.

Permission order must respect Claude Code's current deny → ask → allow behavior.

## Sandbox

If the installed Claude Code version supports a stable sandbox configuration for the current OS, evaluate enabling it with project-scoped filesystem/network restrictions.

Do not enable a sandbox configuration you have not validated against the current Claude Code schema and the project's WordPress/SSH workflow.

Report what was enabled and what remains protected by permissions/hooks instead.

---

# 9. Phase D — create practical safety hooks

Use Claude Code hooks only where they provide a real safeguard or verification advantage.

Do not create hooks that fire expensive full test suites after every minor tool call.

Prefer repository-local hook scripts under:

```text
.claude/hooks/
```

Reuse an existing hook if one already solves the requirement.

## Hook 1 — destructive command guard

Create a `PreToolUse` guard for Bash/PowerShell that blocks or forces confirmation for clearly dangerous commands.

At minimum, protect against relevant variants of:

```text
rm -rf on broad/critical paths
Remove-Item -Recurse/-Force on broad paths
git reset --hard
git clean -fd / -fdx
git push --force / --force-with-lease
wp db drop
wp db reset
unreviewed destructive DB commands
```

For operations that may sometimes be legitimate after approval, prefer an **ask/approval path** rather than making future approved work impossible.

Do not use fragile substring matching when a safer command-aware pattern can be implemented simply.

The hook must:

- parse hook input safely;
- return the correct current Claude Code hook decision format;
- never echo secrets;
- fail safely;
- work in the chosen primary shell/environment;
- be tested with both blocked and allowed commands.

## Hook 2 — post-edit lightweight validation

Evaluate a lightweight `PostToolUse` validation hook for edited/written code files.

It may run only cheap, targeted checks that already exist, for example:

- PHP syntax check for a changed `.php` file when PHP is available;
- JSON parse validation for edited JSON settings;
- optional JS/CSS lint only when the repository already has a configured lightweight lint command.

Do not introduce a large build system just to support this hook.

Do not run full Lighthouse/Playwright suites after every edit.

## Hook 3 — optional completion guard

Only if it can be implemented reliably without creating frustrating loops, evaluate a `Stop` or task-completion hook that reminds/checks for:

- incomplete task TODOs;
- unverified changes;
- stale docs/checklists;
- obvious uncommitted temporary/debug files.

Do not add this hook if it creates more noise than value. Explain the decision.

## Hook verification

Test every installed hook using harmless synthetic commands/events where possible.

A hook is not complete merely because its JSON parses.

---

# 10. Phase E — required project skills

Audit existing `.claude/skills/` first and reuse/update them.

The desired project skill set is:

```text
wp-audit
wp-cli-safe
elementor-build
visual-qa
seo-review
performance-audit
cpanel-audit
backup-verify
release-check
implementation-workflow
git-checkpoint
wordpress-debug
plugin-evaluation
```

Do not create duplicate skills if equivalent functionality already exists.

## Existing core skills

Retain/improve these when already present:

- `wp-audit`
- `wp-cli-safe`
- `elementor-build`
- `visual-qa`
- `seo-review`
- `performance-audit`
- `cpanel-audit`
- `backup-verify`
- `release-check`

## Add `implementation-workflow` if missing

This skill must enforce:

- TODO before implementation;
- repo/reuse scan first;
- minimal implementation;
- correct file ownership;
- edge-case verification;
- dead-code cleanup;
- docs/checklist updates;
- final diff review.

## Add `git-checkpoint` if missing

This skill must:

1. inspect status/diff;
2. ensure verification is complete;
3. detect obvious sensitive/generated files;
4. stage only intended files;
5. propose a concise human commit message;
6. never add AI attribution;
7. commit only when the task/user authorizes a commit;
8. never push unless separately authorized.

## Add `wordpress-debug` if missing

This skill should define a safe debugging order:

1. reproduce;
2. inspect browser console/network if frontend;
3. inspect WordPress/PHP logs without exposing secrets;
4. check plugin/theme conflict evidence;
5. isolate smallest cause;
6. implement smallest fix;
7. test edge cases;
8. remove temporary debugging code.

Do not enable `WP_DEBUG_DISPLAY` on production merely to troubleshoot.

## Add `plugin-evaluation` if missing

Before any WordPress plugin installation, this skill should check:

- whether WordPress/Elementor/custom code already solves the need;
- maintenance/update status;
- compatibility;
- permissions/data access;
- performance impact;
- overlap with existing plugins;
- free vs paid requirement;
- lock-in;
- security implications.

Return INSTALL / DO NOT INSTALL / NEEDS APPROVAL.

---

# 11. Phase F — local development tools

Install only tools that materially improve this project and only after checking whether they already exist.

Prefer installation in the primary environment selected during the audit.

## Git

Required.

If missing, report the safest installation path for the OS and get approval before making system-wide changes unless this task is already being executed with explicit local-install permission.

## WP-CLI

Required for efficient WordPress inspection when the environment supports it.

Use WP-CLI primarily against local/staging first.

Do not expose `wp-config.php` secrets.

If remote cPanel hosting already provides WP-CLI, do not install a redundant server copy.

## Playwright CLI

Recommended and normally required for visual/browser QA once Node.js 20+ is available.

Prefer the official Playwright CLI for coding agents rather than adding Playwright MCP by default.

If Node.js 20+ is present and Playwright CLI is absent, install the current official CLI using the documented method, then install its coding-agent skills.

Verify the installation with a harmless public/local page before using it on client staging.

Do not save production browser authentication state in the repository.

## Lighthouse

Do not create a new Node project solely for Lighthouse.

If an existing project toolchain supports it cleanly, install/use Lighthouse for the performance phase. Otherwise document a safe on-demand method and defer installation until performance QA.

## PHP language intelligence

If this repository contains meaningful custom PHP development, evaluate the official Claude Code PHP LSP plugin from Anthropic's official marketplace.

Install it only if:

- PHP code intelligence will materially help;
- its required language server is present or can be installed safely;
- installation does not create unnecessary global/toolchain complexity.

If custom PHP is currently trivial, skip it and report why.

## GitHub CLI/plugin

If `origin` is GitHub and we will use PRs/issues/review, evaluate:

- GitHub CLI (`gh`), and/or
- the official GitHub Claude Code plugin from Anthropic's official marketplace.

Do not install either merely to create commits.

Use least-privilege authentication and never place tokens in the repo.

---

# 12. Phase G — MCP policy

MCP servers have privileged access. Install only when there is a concrete need that CLI/project tools do not already solve better.

## Initial required MCP set

**None by default.**

A professional setup does not require an MCP server just because Claude supports MCP.

## WordPress MCP Adapter

The official WordPress MCP Adapter may be evaluated later only when:

- the audited WordPress version supports the required Abilities API;
- a specific agent workflow benefits from it more than WP-CLI;
- it is tested on staging first;
- only intentionally exposed abilities are enabled;
- permissions are least privilege;
- the user explicitly approves installing the WordPress-side component.

Do not install it on production as part of this setup task.

## cPanel MCP

Do **not** install a generic third-party cPanel MCP server during this setup.

Reason: cPanel can expose domains, DNS, files, email, databases, SSL, cron, backups, and account-wide resources. That is more privilege than this project requires.

Prefer:

- cPanel UI for account-level administration;
- SSH for controlled shell access;
- WP-CLI for WordPress actions;
- cPanel Git Version Control where available and appropriate;
- WP Toolkit where available for staging/WordPress management.

## SSH/filesystem/database MCP

Do not install generic SSH, unrestricted filesystem, or direct database MCP servers for the client production account.

Claude already has local filesystem/shell capabilities and can use controlled SSH/WP-CLI when approved.

Direct MySQL access is not the default automation path.

## Any future MCP installation

Before installing an MCP server, report:

```text
Name
Official source/maintainer
Exact purpose
Transport
Data/resources exposed
Write capabilities
Credential method
Scope
Why existing CLI/tools are insufficient
Security risk: Low / Medium / High / Critical
Recommendation
```

Verify current documentation/maintenance before installation.

Never install an MCP server by blindly executing an unreviewed `npx -y`, `curl | sh`, remote binary, or arbitrary GitHub command.

---

# 13. Phase H — Claude Code plugin policy

Use Anthropic's official marketplace first.

Do not add community marketplaces by default.

Before plugin installation:

1. confirm a real project need;
2. verify it is from a trusted source;
3. inspect what skills/hooks/MCP/LSP functionality it adds;
4. inspect required binaries/credentials;
5. choose the narrowest appropriate installation scope;
6. report the change.

## Recommended conditional plugins

### PHP LSP

If meaningful custom PHP exists and the required language server is available, the official PHP LSP plugin is recommended.

### GitHub

If this repo is hosted on GitHub and issue/PR/repository operations will be used, the official GitHub plugin may be installed.

Do not install it merely for commits.

### Other plugins

Do not install extra formatting, autonomous deployment, database, browser, or infrastructure plugins unless the audit identifies a concrete benefit.

Do not install demo/community plugins simply because they appear useful.

After installing/enabling plugins, reload them and verify there are no loading/dependency errors.

---

# 14. Phase I — cPanel / SSH setup

This phase is **audit and access preparation only** unless the user has explicitly approved remote configuration changes.

Read `CPANEL-WORKFLOW.md` and use `/cpanel-audit`.

Determine whether the client's hosting provides:

- SSH/Terminal;
- WP-CLI;
- WP Toolkit;
- Git Version Control;
- staging/cloning;
- backups;
- SSL management;
- server cache;
- PHP selector/configuration;
- cron;
- disk quota;
- database management;
- email routing/SMTP dependencies.

## Preferred remote access model

Use a dedicated SSH key for this client/project when SSH is available.

The private key must:

- live outside the Git repository;
- be passphrase protected where practical;
- never be printed/read into Claude context;
- never be committed.

Prefer a local SSH alias such as:

```text
Host easyquran-staging
    HostName <server>
    User <cpanel-user>
    IdentityFile <key-outside-repo>
```

Do not create or modify the user's SSH configuration until the exact host/account is known and the user has approved the setup.

## Remote command rules

Before the first SSH session, state:

- alias/hostname;
- user/account;
- intended target domain;
- local/staging/production classification;
- expected WordPress path;
- whether the planned command is read-only.

First remote checks must be read-only.

Do not make a production write merely because SSH works.

## cPanel Git

If cPanel Git Version Control is available, evaluate it for deployment of custom child-theme/plugin code only.

Do not assume Git represents Elementor/database state.

## WP Toolkit

If available, evaluate WP Toolkit for cloning/staging, backups, and WordPress management.

Do not trigger clone, sync, update, or restore operations during audit without approval.

---

# 15. Phase J — test the Claude setup

Before declaring setup complete, verify the configuration.

## Claude configuration

Verify:

- `CLAUDE.md` loads;
- `.claude/rules/` load;
- skills are discoverable;
- `.claude/settings.json` parses and is recognized;
- `.claude/settings.local.json` remains untracked if present;
- plugin loading has no errors;
- MCP inventory matches the approved policy.

## Hook tests

Test at least:

1. a harmless allowed/read command;
2. a dangerous command that must be blocked;
3. a high-impact command that must request approval rather than execute silently;
4. a PHP/JSON edit validation if that hook was installed.

Do not test a destructive hook using an actually destructive target.

## Git workflow test

Without pushing anything:

- run status/diff checks;
- verify ignored secret/database/backup patterns;
- verify the `git-checkpoint` skill proposes a concise commit message;
- confirm no AI attribution is added.

Do not create a dummy commit merely to test if it would pollute project history. If a setup commit is appropriate, make it only after the setup diff is reviewed and the user/task authorizes the commit.

## Browser tooling test

If Playwright CLI was installed, verify it can:

- start;
- open a harmless local/public test page;
- take a screenshot/snapshot;
- close cleanly.

Do not log into production as part of the tooling test.

## WP-CLI test

If a local/staging WordPress target is already configured and explicitly approved, run read-only checks only.

If no WordPress target is configured yet, verify `wp cli info` and defer site-specific tests.

---

# 16. Required setup documentation

Create/update a concise tool inventory document only if one does not already exist in an appropriate docs file.

Do not create multiple overlapping tool inventories.

The inventory should contain:

| Tool / Skill / Plugin / MCP | Version/source | Scope | Purpose | Access | Risk | Status |
|---|---|---|---|---|---|---|

Include:

- Claude Code version;
- installed project skills;
- hooks;
- plugins;
- MCP servers;
- WP-CLI;
- Playwright CLI;
- optional GitHub tooling;
- cPanel/SSH approach.

Never record credentials or private key paths that reveal sensitive user-specific information unnecessarily.

---

# 17. Final Git checkpoint

After setup implementation and testing:

1. review `git status`;
2. review `git diff --check`;
3. review the full diff;
4. remove temporary/test artifacts;
5. verify `.gitignore`;
6. ensure no secret/private/generated files are staged;
7. update `CLAUDE.md` and completed checklists;
8. propose one clean setup commit message.

Preferred style example:

```text
chore: configure project development safeguards
```

Do not use a long commit body unless needed.

Do not mention AI/Claude in the commit message unless the actual repository convention explicitly names Claude configuration files and such wording is necessary. Prefer describing the development capability rather than the assistant.

Do not push without explicit authorization.

---

# 18. Completion criteria

This task is complete only when:

- [ ] repository/toolchain audit is complete;
- [ ] implementation TODO was maintained;
- [ ] existing assets were reused where appropriate;
- [ ] `CLAUDE.md` is current and under 1000 lines;
- [ ] implementation and Git rules are persisted in `.claude/rules/`;
- [ ] required project skills exist without duplication;
- [ ] shared settings are valid;
- [ ] dangerous operations are protected by permissions/hooks;
- [ ] hooks were tested;
- [ ] Playwright CLI is configured when the prerequisites justify it;
- [ ] WP-CLI strategy is defined/tested where possible;
- [ ] no unnecessary MCP servers were installed;
- [ ] no generic cPanel/SSH/database MCP was installed;
- [ ] optional plugins were installed only when justified;
- [ ] cPanel/SSH workflow is documented without production writes;
- [ ] no secrets were exposed or committed;
- [ ] all temporary/dead setup code was removed;
- [ ] docs/checklists were updated;
- [ ] Git diff is clean and focused;
- [ ] a concise commit message was proposed;
- [ ] no push or production change occurred without explicit approval.

---

# 19. Final report format

When finished, report exactly:

## Setup Summary
Short summary of what was configured.

## Environment
- primary shell/OS
- Claude Code version
- project root
- Git branch/remote state

## Reused Existing Setup
What existing files, skills, configuration, or tooling were reused.

If new components were necessary, state briefly why existing components were insufficient.

## Files Changed
List each file and purpose.

## Skills
- existing retained/updated
- new added
- skipped and why

## Hooks
For each hook:
- event
- purpose
- blocked/ask behavior
- verification performed

## Permissions / Sandbox
What is denied, asked, allowed, and sandboxed.

## Plugins
For each installed/skipped plugin:
- source
- purpose
- scope
- risk
- reason

## MCP Servers
List all configured MCP servers.

If none, explicitly state that none were required and why.

## WordPress Tooling
- WP-CLI state
- PHP/Composer state
- Elementor workflow implications

## Browser QA
- Playwright state
- verification performed

## cPanel / SSH
- capabilities discovered
- safe access model
- staging/production safeguards
- no secrets

## Tests / Edge Cases
List what was actually tested, including hook and configuration edge cases.

## Documentation Updates
List docs/checklists updated and confirm `CLAUDE.md` is current and under 1000 lines.

## Git Review
- status
- diff review
- secret/generated-file check
- proposed concise commit message

## Remaining Manual Actions
Only actions genuinely requiring the user, credentials, hosting UI, approval, or a future project phase.

## Safety Confirmation
Explicitly confirm whether any remote system, production site, DNS, SSL, database, email, or cPanel setting was changed.
