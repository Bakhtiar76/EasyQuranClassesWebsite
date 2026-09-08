# Rebuild sanctioned Elementor page scripts, flush generated CSS, then capture QA.
# Usage: pwsh local/iterate.ps1 -Page 10-home -Route / -Out QA/after
# Use -All after regenerating any inline ornament; Docker requires escalation in Codex.
param(
    [string]$Page = '10-home',
    [string]$Route = '/',
    [string]$Out = 'QA/after',
    [switch]$All,
    [string]$Widths = '1920,1440,1024,768,390'
)
$ErrorActionPreference = 'Stop'
$repoRoot = Split-Path $PSScriptRoot -Parent
Push-Location $repoRoot
try {
    $composeArgs = @('compose', '-f', 'local/docker-compose.yml', '--env-file', 'local/.env', 'run', '--rm', 'wpcli')
    $target = & docker @composeArgs eval 'echo home_url()."|".wp_get_environment_type();'
    if ($LASTEXITCODE -ne 0 -or ($target -join '').Trim() -ne 'http://localhost|local') {
        throw 'Rebuild requires the approved http://localhost site and local PHP environment.'
    }
    if ($All) {
        $builders = @(Get-ChildItem -LiteralPath 'tools/pages' -Filter '*.php' | Sort-Object Name)
    } else {
        if ($Page -notmatch '^\d{2}-[a-z-]+$') { throw 'Page must be a builder basename, e.g. 10-home.' }
        $builders = @(Get-Item -LiteralPath "tools/pages/$Page.php")
    }
    foreach ($builder in $builders) {
        & docker @composeArgs --user=1 eval-file "/tools/pages/$($builder.Name)"
        if ($LASTEXITCODE -ne 0) { throw "Page rebuild failed: $($builder.Name)" }
    }
    & docker @composeArgs elementor flush-css
    if ($LASTEXITCODE -ne 0) { throw 'Elementor CSS flush failed.' }
    & node tests/visual/sweep.mjs http://localhost $Route --out $Out --widths $Widths
    if ($LASTEXITCODE -ne 0) { throw 'Visual sweep reported failures; inspect evidence.' }
} finally {
    Pop-Location
}
