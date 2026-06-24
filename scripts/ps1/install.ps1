function Write-Line {
    Write-Host "--------------------------------------"
}

$GITHUB_DEV="https://github.com/ojgabrielleal"
$GITHUB_REPOSITORY="https://github.com/ojgabrielleal/akiba"

$APP_URL = "http://localhost:8000/"
$DB_HOST = "mysql"
$DB_USERNAME = "root"
$DB_PASSWORD = "root"

Write-Line
Write-Host "Preparing .env file..."
Write-Line

if (-not (Test-Path ".env" -PathType Leaf)) {
    Copy-Item ".env.example" ".env"
}

if (-not (Test-Path ".env.testing" -PathType Leaf)) {
    Copy-Item ".env.testing.example" ".env.testing"
}

$content = Get-Content .env

$content = $content -replace '^APP_URL=.*', "APP_URL=$APP_URL"
$content = $content -replace '^DB_HOST=.*', "DB_HOST=$DB_HOST"
$content = $content -replace '^DB_USERNAME=.*', "DB_USERNAME=$DB_USERNAME"
$content = $content -replace '^DB_PASSWORD=.*', "DB_PASSWORD=$DB_PASSWORD"

$content | Set-Content .env -Encoding UTF8
clear 

Write-Line
Write-Host "Building Docker environment..."
Write-Line

docker compose build

if ($LASTEXITCODE -ne 0) {
    exit 1
}
clear 

Write-Line
Write-Host "Starting containers..."
Write-Line

docker compose up -d

if ($LASTEXITCODE -ne 0) {
    exit 1
}
clear

Write-Line
Write-Host "Installing PHP dependencies..."
Write-Line

docker compose exec laravel composer install

if ($LASTEXITCODE -ne 0) {
    exit 1
}
clear

Write-Line
Write-Host "Generating Laravel app key..."
Write-Line

docker compose exec laravel php artisan key:generate

if ($LASTEXITCODE -ne 0) {
    exit 1
}
clear

Write-Line
Write-Host "Installing Node dependencies..."
Write-Line

docker compose exec node npm install

if ($LASTEXITCODE -ne 0) {
    exit 1
}
clear

Write-Line
Write-Host "Running database migrations..."
Write-Line

docker compose exec laravel php artisan migrate:fresh --seed

if ($LASTEXITCODE -ne 0) {
    exit 1
}

docker compose down

if ($LASTEXITCODE -ne 0) {
    exit 1
}
clear 

Write-Line
Write-Host "Environment configured successfully!"
Write-Line
Write-Host "To start the environment, run:"
Write-Host "  ./scripts/run.ps1 up"
Write-Line
Write-Host "Github Dev: $GITHUB_DEV"
Write-Host "Github Repository: $GITHUB_REPOSITORY"
Write-Line
