$ErrorActionPreference = 'Stop'

$DeveloperUrl = 'https://github.com/ojgabrielleal'
$RootDir = Resolve-Path (Join-Path $PSScriptRoot '..\..')
Set-Location $RootDir

function Step {
    param([string] $Title)

    $Width = 58
    $InnerWidth = $Width - 2
    $TotalPadding = $InnerWidth - $Title.Length
    $LeftPadding = [Math]::Floor($TotalPadding / 2)
    $RightPadding = $TotalPadding - $LeftPadding

    Write-Host ''
    Write-Host ('+' + ('-' * $InnerWidth) + '+')
    Write-Host ('|' + (' ' * $LeftPadding) + $Title + (' ' * $RightPadding) + '|')
    Write-Host ('+' + ('-' * $InnerWidth) + '+')
}

function Print-Success {
    Write-Host ''
    Write-Host 'Akiba setup complete'
    Write-Host '----------------------------------------'
    Write-Host 'Start the project'
    Write-Host '.\scripts\run.ps1 server up'
    Write-Host ''
    Write-Host 'Thank you for installing Akiba.'
    Write-Host "Developer  $DeveloperUrl"
    Write-Host '----------------------------------------'
}

if (Test-Path '.env') {
    Step 'Preparing Akiba environment file'
    $EnvContent = Get-Content '.env'
    $EnvContent -replace '^VITE_HOST=.*', 'VITE_HOST=0.0.0.0' | Set-Content '.env'
}

Step 'Building and starting Akiba containers'
docker compose up --build -d

Step 'Waiting for the database to be ready'
Start-Sleep -Seconds 15

Step 'Installing Laravel dependencies'
docker compose exec laravel composer install

Step 'Installing frontend dependencies'
docker compose exec node npm install

Step 'Generating application key'
docker compose exec laravel php artisan key:generate

Step 'Preparing Akiba database'
docker compose exec laravel php artisan migrate
docker compose exec laravel php artisan db:seed

Print-Success
