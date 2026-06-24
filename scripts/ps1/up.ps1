$GITHUB_DEV="https://github.com/ojgabrielleal"
$GITHUB_REPOSITORY = "https://github.com/ojgabrielleal/akiba"

$APP_URL = "http://localhost:8000"
$PHPMYADMIN_URL = "http://localhost:8080"
$VITE_URL = "http://localhost:5173/@vite/client"
$VITE_WAIT_ATTEMPTS = 90


# VITE STARTING ---------------------------------
function Show-Vite-Diagnostics {
    Write-Host "Vite diagnostics:"
    docker compose exec -T node sh -lc "ps -ef | grep '[v]ite' || true"
    docker compose exec -T node sh -lc "test -f /tmp/vite.log && tail -n 80 /tmp/vite.log || true"
}

function Start-Vite {
    Write-Host "--------------------------------------"
    Write-Host "Starting Vite..."

    try {
        $response = Invoke-WebRequest -Uri $VITE_URL -UseBasicParsing -TimeoutSec 2

        if ($response.StatusCode -eq 200) {
            Write-Host "Vite is already running."
            Write-Host "--------------------------------------"
            return
        }
    } catch {
    }

    docker compose exec -d node sh -lc "npm run dev -- --host 0.0.0.0 > /tmp/vite.log 2>&1"
    if ($LASTEXITCODE -ne 0) { exit 1 }
}

function Wait-For-Vite {
    Write-Host "Waiting for Vite to be ready..."

    for ($attempt = 1; $attempt -le $VITE_WAIT_ATTEMPTS; $attempt++) {
        try {
            $response = Invoke-WebRequest -Uri $VITE_URL -UseBasicParsing -TimeoutSec 2

            if ($response.StatusCode -eq 200) {
                Write-Host "Vite is running."
                Write-Host "--------------------------------------"
                return
            }
        } catch {
        }

        Start-Sleep -Seconds 1
    }

    clear 

    Write-Host "--------------------------------------"
    Write-Host "Vite did not start at $VITE_URL"
    Write-Host "--------------------------------------"

    Show-Vite-Diagnostics
    exit 1
}
# VITE STARTING ---------------------------------

Write-Host "--------------------------------------"
Write-Host "Starting containers..."
Write-Host "--------------------------------------"

docker compose up -d
docker compose exec -d laravel php artisan serve --host=0.0.0.0 --port=8000
Start-Vite
Wait-For-Vite
clear

Write-Host "--------------------------------------"
Write-Host "Environments are starting..."
Write-Host "--------------------------------------"
Start-Sleep -Seconds 30
clear

Write-Host "--------------------------------------"
Write-Host "Environment is running!"
Write-Host "--------------------------------------"
Write-Host "Site: $APP_URL"
Write-Host "PHPMyAdmin: $PHPMYADMIN_URL"
Write-Host "--------------------------------------"
Write-Host "Github Dev: $GITHUB_DEV"
Write-Host "Github Repository: $GITHUB_REPOSITORY"
Write-Host "--------------------------------------"
Write-Host "Panel: $APP_URL/panel"
Write-Host "User: admin"
Write-Host "Pass: admin"
Write-Host "--------------------------------------"
