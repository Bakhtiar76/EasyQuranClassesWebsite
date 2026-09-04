#!/usr/bin/env node
// PreToolUse guard for Bash/PowerShell.
//
// Blocks only the operations CLAUDE.md's "Never Do" list already forbids
// outright and that have no legitimate mid-development use on this project:
// wiping a filesystem root, raw DROP/TRUNCATE SQL, piping a remote download
// into a shell, reading a secret/credential file through a command the
// built-in Read permission checks don't parse, and AI-attribution commit
// trailers. Everything else destructive-but-sometimes-legitimate (force
// push, `git reset --hard`, `wp db drop`, plugin/theme delete, `rsync`) is
// handled by `permissions.ask` in .claude/settings.json, not here — an
// outright deny would make approved work impossible.
//
// Defense-in-depth only: permissions.deny/ask is the primary control. This
// hook fails OPEN on any input it can't parse — it must never block Claude's
// legitimate work because of a bug in itself, and it never echoes file
// contents or secret values.

import { readFileSync } from 'node:fs';
import path from 'node:path';

function readStdin() {
  try {
    return readFileSync(0, 'utf8');
  } catch {
    return '';
  }
}

function allow() {
  process.exit(0);
}

function deny(reason) {
  console.log(
    JSON.stringify({
      hookSpecificOutput: {
        hookEventName: 'PreToolUse',
        permissionDecision: 'deny',
        permissionDecisionReason: reason,
      },
    })
  );
  process.exit(0);
}

// Split a shell command into top-level subcommands on &&, ||, ;, |, |&, &,
// and newlines, ignoring separators inside single/double quotes. Not a full
// shell parser — a heuristic safety net, not the sole line of defense.
function splitSubcommands(cmd) {
  const parts = [];
  let cur = '';
  let quote = null;
  for (let i = 0; i < cmd.length; i++) {
    const c = cmd[i];
    const two = cmd.slice(i, i + 2);
    if (quote) {
      cur += c;
      if (c === quote) quote = null;
      continue;
    }
    if (c === '"' || c === "'") {
      quote = c;
      cur += c;
      continue;
    }
    if (two === '&&' || two === '||' || two === '|&') {
      parts.push(cur);
      cur = '';
      i++;
      continue;
    }
    if (c === ';' || c === '|' || c === '&' || c === '\n') {
      parts.push(cur);
      cur = '';
      continue;
    }
    cur += c;
  }
  parts.push(cur);
  return parts.map((p) => p.trim()).filter(Boolean);
}

function isDangerousTarget(arg, cwd) {
  const a = arg.replace(/^["']|["']$/g, '');
  if (['.', './', ''].includes(a)) return true;
  if (['/', '/*', '~', '~/', '~/*'].includes(a)) return true;
  if (/^[A-Za-z]:[\\/]\*?$/.test(a)) return true; // bare drive root, e.g. C:\ or C:/*
  if (cwd) {
    try {
      if (path.resolve(cwd, a) === path.resolve(cwd)) return true; // resolves to project root
    } catch {
      /* ignore unresolvable paths */
    }
  }
  return false;
}

const SECRET_PATTERN =
  /(^|[\\/])(\.env(\.\w+)?|wp-config\.php|id_rsa\w*|[^\\/]*\.(pem|key|ppk|crt)|\.credentials\.json)$/i;

// .env.example is a committed template with no real values — never a secret.
function isSecretArg(raw) {
  const a = raw.replace(/^["']|["']$/g, '');
  const base = a.split(/[\\/]/).pop() || '';
  if (base === '.env.example') return false;
  return SECRET_PATTERN.test(a);
}

let input;
try {
  input = JSON.parse(readStdin());
} catch {
  allow(); // can't parse hook input — fail open, never block on our own bug
}

const toolName = input.tool_name || '';
if (!/^(Bash|PowerShell)$/.test(toolName)) allow();

const command = (input.tool_input && input.tool_input.command) || '';
if (!command) allow();

const cwd = input.cwd || '';
const subcommands = splitSubcommands(command);

for (const sub of subcommands) {
  // 1. rm -rf / Remove-Item -Recurse -Force targeting a filesystem root.
  const rmMatch = sub.match(/^rm\s+(.+)$/i);
  if (rmMatch) {
    const args = rmMatch[1].split(/\s+/);
    const hasR = args.some((a) => /^-[a-z]*r[a-z]*$/i.test(a) || a === '--recursive');
    const hasF = args.some((a) => /^-[a-z]*f[a-z]*$/i.test(a) || a === '--force');
    const targets = args.filter((a) => !a.startsWith('-'));
    if (hasR && hasF && targets.some((t) => isDangerousTarget(t, cwd))) {
      deny(
        `Blocked: "${sub}" recursively force-removes a filesystem or project root. This is a Never-Do in CLAUDE.md. Run it manually outside Claude Code if genuinely intended.`
      );
    }
  }

  const riMatch = sub.match(/^Remove-Item\s+(.+)$/i);
  if (riMatch) {
    const argStr = riMatch[1];
    const hasRecurse = /-Recurse\b/i.test(argStr);
    const hasForce = /-Force\b/i.test(argStr);
    const targets = argStr.split(/\s+/).filter((a) => !a.startsWith('-'));
    if (hasRecurse && hasForce && targets.some((t) => isDangerousTarget(t, cwd))) {
      deny(
        `Blocked: "${sub}" recursively force-removes a filesystem or project root. This is a Never-Do in CLAUDE.md. Run it manually outside Claude Code if genuinely intended.`
      );
    }
  }

  // 2. Raw destructive SQL, however it's invoked (mysql -e, wp db query, wp eval …).
  if (/\b(drop\s+(database|table)|truncate\s+table|truncate\s+\w+)\b/i.test(sub)) {
    deny(
      `Blocked: "${sub}" contains a DROP/TRUNCATE statement. CLAUDE.md forbids dropping/truncating/resetting database tables directly.`
    );
  }

  // 3. Secret/credential file read via a command the Read permission
  // system doesn't parse (cat/head/tail/sed are already covered natively).
  if (/^(type|get-content|gc|less|more|strings)\b/i.test(sub)) {
    const args = sub.split(/\s+/).slice(1).filter((a) => !a.startsWith('-'));
    if (args.some((a) => isSecretArg(a))) {
      deny(`Blocked: "${sub}" reads a secret/credential file. Never print credential contents.`);
    }
  }
  if (/^(scp|curl)\b/i.test(sub)) {
    const args = sub.split(/\s+/).slice(1).filter((a) => !a.startsWith('-'));
    if (args.some((a) => isSecretArg(a))) {
      deny(`Blocked: "${sub}" appears to transmit a secret/credential file off-machine.`);
    }
  }

  // 4. AI-attribution commit trailers (.claude/rules/git.md).
  if (/^git\s+commit\b/i.test(sub) && /co-authored-by:\s*claude|generated (with|by)\s*(ai|claude)|🤖/i.test(sub)) {
    deny(
      'Blocked: commit message carries AI attribution. .claude/rules/git.md forbids "Generated by Claude" / "Co-Authored-By: Claude" trailers.'
    );
  }
}

// Cross-subcommand check: a remote download piped/chained into a shell.
for (let i = 0; i < subcommands.length - 1; i++) {
  if (/^(curl|wget)\b/i.test(subcommands[i]) && /^(sh|bash|zsh|powershell|pwsh)\b/i.test(subcommands[i + 1])) {
    deny(
      `Blocked: "${subcommands[i]} | ${subcommands[i + 1]}" pipes a remote download directly into a shell. CLAUDE.md forbids executing downloaded scripts blindly.`
    );
  }
}

allow();
