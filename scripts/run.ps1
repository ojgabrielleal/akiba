param (
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]]$CommandArgs
)

$SCRIPT_DIR = Split-Path -Parent $MyInvocation.MyCommand.Path

function Show-Help {
    Write-Host "Usage:"
    Write-Host "  ./scripts/run.ps1 install"
    Write-Host "  ./scripts/run.ps1 up"
    Write-Host "  ./scripts/run.ps1 down"
    Write-Host "  ./scripts/run.ps1 artisan ..."
    Write-Host "  ./scripts/run.ps1 node ..."
    Write-Host "  ./scripts/run.ps1 composer ..."

}

if (-not $CommandArgs -or $CommandArgs.Count -eq 0) {
    Show-Help
    exit 0
}

$command = $CommandArgs[0].ToLowerInvariant()
$rest = @()

if ($CommandArgs.Count -gt 1) {
    $rest = $CommandArgs[1..($CommandArgs.Count - 1)]
}

switch ($command) {
    "install" {
        & "$SCRIPT_DIR\ps1\install.ps1" @rest
    }
    "up" {
        & "$SCRIPT_DIR\ps1\up.ps1" @rest
    }
    "down" {
        & "$SCRIPT_DIR\ps1\down.ps1" @rest
    }
    "artisan" {
        docker compose exec laravel php artisan @rest
    }
    "composer" {
        docker compose exec laravel composer @rest
    }
    "node" {
        docker compose exec node npm @rest
    }
    "npm" {
        docker compose exec node npm @rest
    }
    "laravel" {
        docker compose exec laravel @rest
    }
    "docker" {
        docker compose @rest
    }
    default {
        Write-Host "Unknown command: $command"
        Write-Host ""
        Show-Help
        exit 1
    }
}

if ($LASTEXITCODE -ne 0) {
    exit $LASTEXITCODE
}
