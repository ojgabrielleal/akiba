$APP_URL = "http://localhost:8000"
$PHPMYADMIN_URL = "http://localhost:8080"
$GITHUB_REPOSITORY = "https://github.com/ojgabrielleal/akiba"

function Test-UrlAvailable {
    param (
        [string]$Url
    )

    try {
        Invoke-WebRequest -Uri $Url -UseBasicParsing -TimeoutSec 2 | Out-Null
        return $true
    }
    catch {
        return $false
    }
}

function Test-ViteRunning {
    docker compose exec node sh -lc "ps aux | grep -E '[v]ite|[n]pm run dev' >/dev/null 2>&1"
    return $LASTEXITCODE -eq 0
}

function Wait-ForVite {
    for ($attempt = 1; $attempt -le 30; $attempt++) {
        if (Test-ViteRunning) {
            return $true
        }

        Start-Sleep -Seconds 2
    }

    return $false
}

Write-Host "--------------------------------------"
Write-Host "Starting containers..."
Write-Host "--------------------------------------"

docker compose up -d

if ($LASTEXITCODE -ne 0) {
    exit 1
}

# Start Laravel only when the app is not already responding.
if (-not (Test-UrlAvailable $APP_URL)) {
    docker compose exec -d laravel php artisan serve --host=0.0.0.0 --port=8000

    if ($LASTEXITCODE -ne 0) {
        exit 1
    }
}

# Start Vite only when its dev server process is not already running.
if (-not (Test-ViteRunning)) {
    docker compose exec -d node npm run dev -- --host 0.0.0.0

    if ($LASTEXITCODE -ne 0) {
        exit 1
    }
}

# Wait until the Vite process exists before printing the ready links.
if (-not (Wait-ForVite)) {
    Write-Host "Vite dev server is not running inside the node container."
    Write-Host "Run this command to check the logs:"
    Write-Host "   docker compose logs node"
    exit 1
}

# Give Docker services a short warm-up before printing clickable links.
Write-Host "--------------------------------------"
Write-Host "Waiting for services to become available..."
Write-Host "--------------------------------------"
Start-Sleep -Seconds 30
clear

Write-Host "--------------------------------------"
Write-Host "Environment is running!"
Write-Host "--------------------------------------"
Write-Host "Site: $APP_URL"
Write-Host "PHPMyAdmin: $PHPMYADMIN_URL"
Write-Host "Github Repository: $GITHUB_REPOSITORY"
Write-Host "--------------------------------------"
Write-Host "Panel: $APP_URL/panel"
Write-Host "User: admin"
Write-Host "Pass: admin"
Write-Host "--------------------------------------"
