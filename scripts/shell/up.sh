#!/bin/bash

set -e

APP_URL="http://localhost:8000"
PHPMYADMIN_URL="http://localhost:8080"
GITHUB_REPOSITORY="https://github.com/ojgabrielleal/akiba"

line() {
    echo "--------------------------------------"
}

is_available() {
    curl -fsS "$1" >/dev/null 2>&1
}

vite_is_running() {
    docker compose exec node sh -lc "ps aux | grep -E '[v]ite|[n]pm run dev' >/dev/null 2>&1"
}

wait_for_vite() {
    for attempt in {1..30}; do
        if vite_is_running; then
            return 0
        fi

        sleep 2
    done

    return 1
}

line
echo "Starting containers..."
line

docker compose up -d

# Start Laravel only when the app is not already responding.
if ! is_available "$APP_URL"; then
    docker compose exec -d laravel php artisan serve --host=0.0.0.0 --port=8000
fi

# Start Vite only when its dev server process is not already running.
if ! vite_is_running; then
    docker compose exec -d node npm run dev -- --host 0.0.0.0
fi

# Wait until the Vite process exists before printing the ready links.
if ! wait_for_vite; then
    echo "Vite dev server is not running inside the node container."
    echo "Run this command to check the logs:"
    echo "   docker compose logs node"
    exit 1
fi

# Give Docker services a short warm-up before printing clickable links.
line
echo "Waiting for services to become available..."
line
sleep 30
clear

line
echo "Environment is running!"
line
echo "Site: $APP_URL"
echo "PHPMyAdmin: $PHPMYADMIN_URL"
echo "Github Repository: $GITHUB_REPOSITORY"
line
echo "Panel: $APP_URL/panel"
echo "User: admin"
echo "Pass: admin"
line
