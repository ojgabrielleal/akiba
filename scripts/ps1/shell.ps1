$ErrorActionPreference = 'Stop'

$RootDir = Resolve-Path (Join-Path $PSScriptRoot '..\..')
Set-Location $RootDir

$Service = if ($args.Count -gt 0) { $args[0] } else { 'laravel' }

docker compose exec $Service sh
