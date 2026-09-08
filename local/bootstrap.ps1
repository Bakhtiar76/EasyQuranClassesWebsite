# One-command first-run local setup for a fresh clone: brings up Docker,
# installs WordPress core if needed, then hands off to
# tools/05-bootstrap.php for everything else (plugins, parent theme,
# media import, page content). See README-SETUP.md "Working with a
# teammate". Safe to re-run — every step it touches is idempotent.
#
# Bash/macOS/Linux equivalent: local/bootstrap.sh (keep both in sync).
#
# Usage: .\local\bootstrap.ps1

$ErrorActionPreference = 'Stop'
$repoRoot = Split-Path -Parent $PSScriptRoot
Set-Location $repoRoot

Write-Host '=== Starting Docker services ==='
docker compose -f local/docker-compose.yml up -d
if ($LASTEXITCODE -ne 0) { throw 'docker compose up failed - is Docker Desktop running?' }

if (-not (Test-Path local/.env)) {
    Write-Host 'local/.env not found - copying from local/.env.example (see README-SETUP.md 2.1 to customize).'
    Copy-Item local/.env.example local/.env
}

# WP_ADMIN_* is not in the wpcli service's own `environment:` block in
# docker-compose.yml (only WORDPRESS_DB_* is) — Docker Compose substitutes
# ${VAR} into the YAML at parse time, but nothing forwards WP_ADMIN_* into
# the container's process environment, so `wp core install` never sees it
# unless passed explicitly on the command line, read from local/.env here.
$envVars = @{}
Get-Content local/.env | ForEach-Object {
    if ($_ -match '^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*?)\s*$') {
        $envVars[$matches[1]] = $matches[2]
    }
}
$adminUser  = if ($envVars.ContainsKey('WP_ADMIN_USER'))     { $envVars['WP_ADMIN_USER'] }     else { 'admin' }
$adminPass  = if ($envVars.ContainsKey('WP_ADMIN_PASSWORD')) { $envVars['WP_ADMIN_PASSWORD'] } else { 'change-me-locally' }
$adminEmail = if ($envVars.ContainsKey('WP_ADMIN_EMAIL'))    { $envVars['WP_ADMIN_EMAIL'] }    else { 'admin@example.test' }

Write-Host '=== Waiting for WordPress + the database to be reachable ==='
$ready = $false
for ($i = 0; $i -lt 20; $i++) {
    docker compose -f local/docker-compose.yml run --rm wpcli core is-installed *> $null
    if ($LASTEXITCODE -eq 0 -or $LASTEXITCODE -eq 1) { $ready = $true; break }
    Start-Sleep -Seconds 3
}
if (-not $ready) {
    throw 'WordPress/database did not become reachable in time. Check "docker compose -f local/docker-compose.yml logs".'
}

docker compose -f local/docker-compose.yml run --rm wpcli core is-installed *> $null
if ($LASTEXITCODE -eq 0) {
    Write-Host 'WordPress core already installed.'
} else {
    Write-Host '=== Installing WordPress core ==='
    docker compose -f local/docker-compose.yml run --rm wpcli core install `
        --url=http://localhost --title="Easy Quran Classes" `
        --admin_user=$adminUser --admin_password=$adminPass --admin_email=$adminEmail --skip-email
    if ($LASTEXITCODE -ne 0) { throw 'wp core install failed.' }
}

Write-Host '=== Plugins, theme, media, pages (tools/05-bootstrap.php) ==='
docker compose -f local/docker-compose.yml run --rm wpcli --user=1 eval-file /tools/05-bootstrap.php
if ($LASTEXITCODE -ne 0) { throw 'tools/05-bootstrap.php failed - see output above.' }

Write-Host ''
Write-Host "Done. Site: http://localhost/   Admin: http://localhost/wp-admin/  (user: $adminUser)"
