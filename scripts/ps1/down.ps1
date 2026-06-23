Write-Host "--------------------------------------"
Write-Host "Stopping containers..."
Write-Host "--------------------------------------"

docker compose down

if ($LASTEXITCODE -ne 0) {
    exit 1
}

Write-Host "--------------------------------------"
Write-Host "Environment stopped successfully!"
Write-Host "--------------------------------------"
