param(
    [Parameter(Position = 0)]
    [string] $Command = 'help',

    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]] $ScriptArgs = @()
)

$ErrorActionPreference = 'Stop'

$ScriptDir = $PSScriptRoot

function Show-Usage {
    Write-Host 'Usage: .\scripts\run.ps1 <command> [args]'
    Write-Host ''
    Write-Host 'Commands: install, server, laravel, node, composer, shell'
    Write-Host ''
    Write-Host 'Examples:'
    Write-Host '.\scripts\run.ps1 install'
    Write-Host '.\scripts\run.ps1 server up'
    Write-Host '.\scripts\run.ps1 laravel php artisan migrate'
    Write-Host '.\scripts\run.ps1 node npm install'
    Write-Host '.\scripts\run.ps1 shell node'
}

switch ($Command) {
    { $_ -in @('help', '--help', '-h') } {
        Show-Usage
        exit 0
    }
    { $_ -in @('install', 'server', 'laravel', 'node', 'composer', 'shell') } {
        & (Join-Path $ScriptDir "ps1\$Command.ps1") @ScriptArgs
        exit $LASTEXITCODE
    }
    default {
        Write-Error "Unknown Akiba command: $Command"
        Show-Usage
        exit 1
    }
}
