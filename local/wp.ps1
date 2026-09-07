# Wrapper for running WP-CLI against the local Docker WordPress site.
# Usage: .\local\wp.ps1 plugin list
#        .\local\wp.ps1 theme activate easy-quran-classes-child
param(
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]]$WpArgs
)
docker compose -f "$PSScriptRoot\docker-compose.yml" run --rm wpcli @WpArgs
