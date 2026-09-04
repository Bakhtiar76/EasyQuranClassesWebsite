#!/usr/bin/env node
// PostToolUse: lightweight validation for a file Claude just wrote/edited.
// Cheap, targeted checks only — no build system, no linters, no test runs.
// Cannot block (the write already happened); it only surfaces a problem
// to Claude via systemMessage.

import { readFileSync, existsSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
import path from 'node:path';

function readStdin() {
  try {
    return readFileSync(0, 'utf8');
  } catch {
    return '';
  }
}

function report(message) {
  console.log(
    JSON.stringify({
      hookSpecificOutput: { hookEventName: 'PostToolUse' },
      systemMessage: message,
    })
  );
}

let input;
try {
  input = JSON.parse(readStdin());
} catch {
  process.exit(0); // can't parse hook input — no-op
}

const filePath = input.tool_input && input.tool_input.file_path;
if (!filePath || !existsSync(filePath)) process.exit(0);

const ext = path.extname(filePath).toLowerCase();

if (ext === '.json') {
  try {
    JSON.parse(readFileSync(filePath, 'utf8'));
  } catch (err) {
    report(`Invalid JSON in ${filePath}: ${err.message}`);
  }
  process.exit(0);
}

if (ext === '.php') {
  try {
    execFileSync('php', ['-l', filePath], { stdio: 'pipe' });
  } catch (err) {
    if (err.code === 'ENOENT') {
      process.exit(0); // php not installed locally — nothing to check
    }
    const output = (err.stdout || err.stderr || Buffer.from(String(err.message))).toString();
    report(`PHP syntax error in ${filePath}:\n${output.trim()}`);
  }
  process.exit(0);
}

process.exit(0);
