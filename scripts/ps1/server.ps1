$ErrorActionPreference = 'Stop'

$RootDir = Resolve-Path (Join-Path $PSScriptRoot '..\..')
Set-Location $RootDir

$AppUrl = 'http://localhost:8000'
$PanelUrl = 'http://localhost:8000/panel'
$PhpMyAdminUrl = 'http://localhost:8080'
$DeveloperUrl = 'https://github.com/ojgabrielleal'

function Print-Links {
    Write-Host ''
    Write-Host 'Akiba is Ready!!'
    Write-Host '----------------------------------------'
    Write-Host "Site       $AppUrl"
    Write-Host "Panel      $PanelUrl"
    Write-Host "PHPMyAdmin $PhpMyAdminUrl"
    Write-Host ''
    Write-Host 'Admin panel credentials'
    Write-Host 'User       admin'
    Write-Host 'Password   admin'
    Write-Host ''
    Write-Host 'Available scripts'
    Write-Host '.\scripts\run.ps1 server up        Start project'
    Write-Host '.\scripts\run.ps1 server down      Stop project'
    Write-Host '.\scripts\run.ps1 server restart   Restart project'
    Write-Host '.\scripts\run.ps1 laravel <cmd>    Run command in Laravel container'
    Write-Host '.\scripts\run.ps1 node <cmd>       Run command in Node container'
    Write-Host '.\scripts\run.ps1 composer <cmd>   Run command in composer'
    Write-Host '.\scripts\run.ps1 shell [service]  Open shell in a service'
    Write-Host ''
    Write-Host 'Examples'
    Write-Host '.\scripts\run.ps1 laravel php artisan migrate'
    Write-Host '.\scripts\run.ps1 node npm install'
    Write-Host '.\scripts\run.ps1 composer require vendor/package'
    Write-Host '.\scripts\run.ps1 shell node'
    Write-Host ''
    Write-Host 'Thank you for using Akiba.'
    Write-Host "Developer  $DeveloperUrl"
    Write-Host '----------------------------------------'
}

function Stop-ProcessInContainer {
    param(
        [string] $Service,
        [string] $PidFile,
        [string] $Name
    )

    $Command = @"
if [ -f '$PidFile' ]; then
    PID=`$(cat '$PidFile')
    if kill "`$PID" 2>/dev/null; then
        echo 'Stopped $Name'
    fi
    rm -f '$PidFile'
fi
"@

    docker compose exec $Service sh -c $Command
}

function Start-Laravel {
    Stop-ProcessInContainer laravel 'storage/logs/artisan-serve.pid' 'Laravel server'

    docker compose exec -d laravel sh -c @'
php artisan serve --host=0.0.0.0 --port=8000 > storage/logs/artisan-serve.log 2>&1 &
echo $! > storage/logs/artisan-serve.pid
wait $!
'@
}

function Start-Node {
    Stop-ProcessInContainer node 'storage/logs/vite.pid' 'Vite server'

    docker compose exec -d node sh -c @'
npm run dev -- --host 0.0.0.0 --port 5173 > storage/logs/vite.log 2>&1 &
echo $! > storage/logs/vite.pid
wait $!
'@
}

$Command = if ($args.Count -gt 0) { $args[0] } else { 'up' }

switch ($Command) {
    'up' {
        Write-Host 'Starting Docker containers'
        docker compose up -d

        Write-Host 'Starting Laravel server'
        Start-Laravel

        Write-Host 'Starting Vite server'
        Start-Node

        Print-Links
    }
    'down' {
        Write-Host 'Stopping Docker containers'
        docker compose down
        Remove-Item -Path 'storage/logs/artisan-serve.pid', 'storage/logs/vite.pid' -Force -ErrorAction SilentlyContinue
    }
    'restart' {
        & $PSCommandPath down
        & $PSCommandPath up
    }
    default {
        Write-Host "Usage: $PSCommandPath [up|down|restart]"
        exit 1
    }
}
