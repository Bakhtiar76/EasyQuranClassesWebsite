<#
.SYNOPSIS
  One-time Codex CLI setup for the Easy Quran Classes local WordPress project.

.DESCRIPTION
  Brings the Codex CLI to parity with Claude Code on this repo:
    1. Preflight  - Codex installed, logged in, NOVAMIRA_APP_PASSWORD present.
    2. MCP        - registers novamira-localhost, chrome-devtools, context7 and
                    playwright in ~/.codex/config.toml (remove-then-add, so
                    re-runs are clean).
    3. Skills     - syncs every .claude/skills/<name> into $CODEX_HOME/skills as
                    eqc-<name> (directory junction; falls back to a copy).
    4. Trust      - ensures this repo path is trust_level = "trusted".

  Idempotent: safe to run repeatedly. Run from a NORMAL terminal, not from
  inside a `codex exec` sandbox (the sandbox hides Docker and $CODEX_HOME).

.PARAMETER Verify
  Skip all changes; just print the parity matrix and exit non-zero on any FAIL.

.EXAMPLE
  pwsh tools/codex/setup-codex.ps1
  pwsh tools/codex/setup-codex.ps1 -Verify
#>
[CmdletBinding()]
param(
  [switch]$Verify
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

# --- paths --------------------------------------------------------------------
$RepoRoot   = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
$CodexHome  = if ($env:CODEX_HOME) { $env:CODEX_HOME } else { Join-Path $HOME '.codex' }
$ConfigToml = Join-Path $CodexHome 'config.toml'
$SkillsSrc  = Join-Path $RepoRoot '.claude\skills'
$SkillsDst  = Join-Path $CodexHome 'skills'
$Wrapper    = Join-Path $PSScriptRoot 'novamira-mcp.cmd'
$LocalEnv   = Join-Path $RepoRoot 'local\.env'

function Test-NovamiraSecret {
  # The wrapper takes the password from local/.env first, env var second
  # (Codex strips *PASSWORD* env vars before spawning MCP servers).
  if (Test-Path $LocalEnv) {
    if (Select-String -Path $LocalEnv -Pattern '^\s*NOVAMIRA_APP_PASSWORD=\S' -Quiet) { return $true }
  }
  return [bool]$env:NOVAMIRA_APP_PASSWORD
}

$script:Fail = 0
function Ok   ($m) { Write-Host "  [ OK ] $m" -ForegroundColor Green }
function Warn ($m) { Write-Host "  [WARN] $m" -ForegroundColor Yellow }
function Bad  ($m) { Write-Host "  [FAIL] $m" -ForegroundColor Red; $script:Fail++ }
function Head ($m) { Write-Host "`n$m" -ForegroundColor Cyan }

# Production origins the Playwright MCP browser must never load. This is
# defence-in-depth for CLAUDE.md's production boundary, not a security
# boundary: Playwright's own docs note the blocklist does not affect
# redirects. The rule ("never point automation at production") still stands
# on its own; this just makes the common accident fail loudly.
$BlockedOrigins = @(
  'https://easyquranclasses.com'
  'http://easyquranclasses.com'
  'https://www.easyquranclasses.com'
  'http://www.easyquranclasses.com'
) -join ';'

# The four project MCP servers. Each command array is passed after `-- `.
# Every entry is spawned through `cmd /c`: Codex on Windows cannot start a
# .cmd/.bat (npx.cmd, the wrapper) directly with working stdio pipes - the MCP
# stdstreams silently go dead. `cmd /c npx ...` resolves npx via PATHEXT and
# keeps the pipes intact. (`npx`, not `npx.cmd`, so no PowerShell policy block.)
$Servers = [ordered]@{
  'novamira-localhost' = @('cmd', '/c', $Wrapper)
  'chrome-devtools'    = @('cmd', '/c', 'npx', '-y', 'chrome-devtools-mcp@latest')
  'context7'           = @('cmd', '/c', 'npx', '-y', '@upstash/context7-mcp')
  'playwright'         = @('cmd', '/c', 'npx', '-y', '@playwright/mcp@latest', '--isolated', '--blocked-origins', $BlockedOrigins)
}

function Get-CodexMcpList {
  # `codex mcp list` must run outside a sandbox; capture text, tolerate failure.
  try { return (& codex mcp list 2>&1 | Out-String) } catch { return '' }
}

# ---------------------------------------------------------------------------
# VERIFY-ONLY
# ---------------------------------------------------------------------------
if ($Verify) {
  Head 'Codex parity check'

  try { $v = (& codex --version 2>&1 | Out-String).Trim(); Ok "codex: $v" }
  catch { Bad 'codex CLI not found on PATH' }

  if (Test-Path (Join-Path $CodexHome 'auth.json')) { Ok 'codex auth.json present' }
  else { Bad 'not logged in - run:  codex login' }

  if (Test-NovamiraSecret) { Ok 'Novamira Application Password available (local/.env or env var)' }
  else { Bad 'no Novamira password - add NOVAMIRA_APP_PASSWORD=... to local/.env (see README-SETUP.md)' }

  $mcp = Get-CodexMcpList
  foreach ($name in $Servers.Keys) {
    if ($mcp -match [regex]::Escape($name)) { Ok "MCP registered: $name" }
    else { Bad "MCP missing: $name  (run this script without -Verify)" }
  }

  if (Test-Path $SkillsDst) {
    $n = @(Get-ChildItem $SkillsDst -Directory -Filter 'eqc-*' -ErrorAction SilentlyContinue).Count
    if ($n -ge 1) { Ok "synced skills: $n (eqc-*)" } else { Bad 'no eqc-* skills synced' }
  } else { Bad "skills dir absent: $SkillsDst" }

  if (Test-Path (Join-Path $RepoRoot 'AGENTS.md')) { Ok 'AGENTS.md present' }
  else { Bad 'AGENTS.md missing at repo root' }

  # Docker + site (this script runs outside the sandbox, so these should work)
  try {
    $ps = & docker compose -f (Join-Path $RepoRoot 'local\docker-compose.yml') ps 2>&1 | Out-String
    if ($ps -match 'wordpress' -and $ps -match 'db') { Ok 'docker: wordpress + db containers listed' }
    else { Warn "docker compose ps did not show both services:`n$ps" }
  } catch { Bad 'docker unreachable from this terminal (start Docker Desktop / the stack)' }

  try {
    $code = (& curl.exe -s -o NUL -w '%{http_code}' http://localhost/ 2>&1).Trim()
    if ($code -eq '200') { Ok 'http://localhost/ -> 200' } else { Bad "http://localhost/ -> $code" }
  } catch { Bad 'could not reach http://localhost/' }

  Head ($(if ($script:Fail -eq 0) { 'PARITY OK' } else { "PARITY INCOMPLETE - $($script:Fail) failing check(s)" }))
  exit $script:Fail
}

# ---------------------------------------------------------------------------
# 1. PREFLIGHT
# ---------------------------------------------------------------------------
Head '1. Preflight'
try { $v = (& codex --version 2>&1 | Out-String).Trim(); Ok "codex: $v" }
catch { Bad 'codex CLI not found. Install it and re-run:  npm install -g @openai/codex'; exit 1 }

if (Test-Path (Join-Path $CodexHome 'auth.json')) { Ok 'codex logged in' }
else { Warn 'not logged in - run `codex login` before using Codex (setup continues)' }

if (Test-NovamiraSecret) {
  Ok 'Novamira Application Password available (local/.env or env var)'
} else {
  Warn 'No Novamira Application Password found.'
  Warn 'The novamira-localhost server will fail until you add this line to local/.env:'
  Warn '  NOVAMIRA_APP_PASSWORD=<application-password>'
  Warn '  (same value .mcp.json uses for Claude Code - see README-SETUP.md)'
}

if (-not (Test-Path $Wrapper)) { Bad "launcher missing: $Wrapper"; exit 1 }
if (-not (Test-Path $ConfigToml)) { Bad "codex config not found: $ConfigToml (run `codex` once to create it)"; exit 1 }

# ---------------------------------------------------------------------------
# 2. MCP SERVERS  (remove-then-add for idempotency)
# ---------------------------------------------------------------------------
Head '2. Register MCP servers'
foreach ($name in $Servers.Keys) {
  & codex mcp remove $name 2>&1 | Out-Null   # ok if not present
  $cmd = $Servers[$name]
  try {
    & codex mcp add $name -- @cmd 2>&1 | Out-Null
    Ok "added: $name  ->  $($cmd -join ' ')"
  } catch {
    Bad "could not add $name : $_"
  }
}

# ---------------------------------------------------------------------------
# 3. SYNC PROJECT SKILLS  -> $CODEX_HOME/skills/eqc-<name>
# ---------------------------------------------------------------------------
Head '3. Sync project skills'
if (-not (Test-Path $SkillsSrc)) {
  Bad "source skills dir not found: $SkillsSrc"
} else {
  if (-not (Test-Path $SkillsDst)) { New-Item -ItemType Directory -Path $SkillsDst | Out-Null }
  $synced = 0
  foreach ($dir in Get-ChildItem $SkillsSrc -Directory) {
    if (-not (Test-Path (Join-Path $dir.FullName 'SKILL.md'))) { continue }
    $target = Join-Path $SkillsDst ("eqc-" + $dir.Name)
    if (Test-Path $target) { Remove-Item $target -Recurse -Force }
    $mode = 'junction'
    try {
      New-Item -ItemType Junction -Path $target -Value $dir.FullName -ErrorAction Stop | Out-Null
    } catch {
      $mode = 'copy'
      Copy-Item $dir.FullName $target -Recurse -Force
    }
    $synced++
    Write-Host ("  [{0,-8}] eqc-{1}" -f $mode, $dir.Name)
  }
  Ok "$synced skill(s) synced to $SkillsDst"
  # prune stale eqc-* entries whose source was removed
  foreach ($old in Get-ChildItem $SkillsDst -Directory -Filter 'eqc-*' -ErrorAction SilentlyContinue) {
    $srcName = $old.Name -replace '^eqc-', ''
    if (-not (Test-Path (Join-Path $SkillsSrc $srcName))) {
      Remove-Item $old.FullName -Recurse -Force
      Warn "pruned stale skill: $($old.Name)"
    }
  }
}

# ---------------------------------------------------------------------------
# 4. TRUST THIS PROJECT
# ---------------------------------------------------------------------------
Head '4. Trust project path'
$pathKey = $RepoRoot.ToLower()
$toml = Get-Content $ConfigToml -Raw
# match [projects.'...'] case-insensitively on the path
$escaped = [regex]::Escape($pathKey)
if ($toml -imatch "\[projects\.'$escaped'\]") {
  Ok "already trusted: $pathKey"
} else {
  $block = "`n[projects.'$pathKey']`ntrust_level = `"trusted`"`n"
  Add-Content -Path $ConfigToml -Value $block -Encoding UTF8
  Ok "added trust entry for: $pathKey"
}

# ---------------------------------------------------------------------------
Head 'Done. Verify with:'
Write-Host '  pwsh tools/codex/setup-codex.ps1 -Verify'
Write-Host '  codex mcp list          # run outside any codex sandbox'
exit 0
