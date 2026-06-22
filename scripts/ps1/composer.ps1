$ErrorActionPreference = 'Stop'

$RootDir = Resolve-Path (Join-Path $PSScriptRoot '..\..')
Set-Location $RootDir

docker compose exec laravel composer @args
