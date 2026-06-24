#!/bin/bash

set -e

line() {
    echo "--------------------------------------"
}

GITHUB_DEV="https://github.com/ojgabrielleal"
GITHUB_REPOSITORY="https://github.com/ojgabrielleal/akiba"

APP_URL="http://localhost:8000"
PHPMYADMIN_URL="http://localhost:8080"
VITE_URL="http://localhost:5173/@vite/client"
VITE_WAIT_ATTEMPTS=90

# VITE STARTING ---------------------------------
show_vite_diagnostics() {
    line
    echo "Vite is not start:"
    docker compose exec -T node sh -lc "ps -ef | grep '[v]ite' || true"
    docker compose exec -T node sh -lc "test -f /tmp/vite.log && tail -n 80 /tmp/vite.log || true"
    line
}

start_vite() {
    line
    echo "Starting Vite..."

    if curl -fs --max-time 2 "$VITE_URL" >/dev/null 2>&1; then
        echo "Vite is already running."
        line
        return 0
    fi

    docker compose exec -d node sh -lc "npm run dev -- --host 0.0.0.0 > /tmp/vite.log 2>&1"
}

wait_for_vite() {
    echo "Waiting for Vite to be ready..."

    for attempt in $(seq 1 "$VITE_WAIT_ATTEMPTS"); do
        if curl -fs --max-time 2 "$VITE_URL" >/dev/null 2>&1; then
            echo "Vite is running."
            line
            return 0
        fi

        sleep 1
    done
    clear 
    
    show_vite_diagnostics
    exit 1
}
# VITE STARTING ---------------------------------

line
echo "Starting containers..."
line

docker compose up -d
docker compose exec -d laravel php artisan serve --host=0.0.0.0 --port=8000
start_vite
wait_for_vite

line
echo "Environments are starting..."
line
sleep 30
clear 

line
echo "Environment is running!"
line
echo "Site: $APP_URL"
echo "PHPMyAdmin: $PHPMYADMIN_URL"
line
echo "Github Dev: $GITHUB_DEV"
echo "Github Repository: $GITHUB_REPOSITORY"
line
echo "Panel: $APP_URL/panel"
echo "User: admin"
echo "Pass: admin"
line
