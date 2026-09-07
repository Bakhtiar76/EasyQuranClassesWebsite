@echo off
REM ---------------------------------------------------------------------------
REM Novamira MCP launcher for the Codex CLI (local WordPress site only).
REM
REM Why a wrapper:
REM  - Codex stores MCP `env` values as literal strings in ~/.codex/config.toml
REM    with NO ${VAR} substitution, so a password there would be written to disk.
REM  - Codex ALSO strips environment variables whose name matches *PASSWORD* /
REM    *SECRET* / *TOKEN* before spawning an MCP server, so the Windows User var
REM    NOVAMIRA_APP_PASSWORD does not reach this process either.
REM
REM So this wrapper reads the Application Password from local\.env (gitignored,
REM created during normal Docker setup). It falls back to the NOVAMIRA_APP_PASSWORD
REM env var if that somehow survives. The secret is never written to Git or Codex
REM config. See README-SETUP.md.
REM ---------------------------------------------------------------------------
setlocal enabledelayedexpansion

set "ENVFILE=%~dp0..\..\local\.env"
set "WP_API_PASSWORD=%NOVAMIRA_APP_PASSWORD%"

if not "%WP_API_PASSWORD%"=="" goto :havepw
if not exist "%ENVFILE%" goto :nopw
for /f "usebackq tokens=1,* delims==" %%A in (`findstr /b /c:"NOVAMIRA_APP_PASSWORD=" "%ENVFILE%"`) do set "WP_API_PASSWORD=%%B"

:havepw
if defined WP_API_PASSWORD set "WP_API_PASSWORD=!WP_API_PASSWORD:"=!"
if not "%WP_API_PASSWORD%"=="" goto :run

:nopw
echo [novamira-mcp] ERROR: no Novamira Application Password found.>&2
echo [novamira-mcp] Add a NOVAMIRA_APP_PASSWORD line to local\.env - see README-SETUP.md - then restart Codex.>&2
exit /b 1

:run
if "%NOVAMIRA_WP_URL%"=="" set "NOVAMIRA_WP_URL=http://localhost/wp-json/mcp/novamira"
if "%NOVAMIRA_WP_USER%"=="" set "NOVAMIRA_WP_USER=admin"
set "WP_API_URL=%NOVAMIRA_WP_URL%"
set "WP_API_USERNAME=%NOVAMIRA_WP_USER%"

REM `call` is required: without it, batch-to-batch chaining drops the redirected
REM stdio pipes the MCP client needs. `npx` (not npx.cmd) so PATHEXT resolves it
REM under cmd without the PowerShell execution-policy block.
call npx -y @automattic/mcp-wordpress-remote@latest %*
exit /b %ERRORLEVEL%
