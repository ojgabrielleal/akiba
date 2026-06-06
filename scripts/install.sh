#!/bin/sh
set -e

DEVELOPER_URL="https://github.com/ojgabrielleal"

step() {
    TITLE="$1"
    WIDTH=58
    INNER_WIDTH=$((WIDTH - 2))
    TITLE_LENGTH=${#TITLE}
    TOTAL_PADDING=$((INNER_WIDTH - TITLE_LENGTH))
    LEFT_PADDING=$((TOTAL_PADDING / 2))
    RIGHT_PADDING=$((TOTAL_PADDING - LEFT_PADDING))

    echo ""
    printf '+%*s+\n' "$INNER_WIDTH" '' | tr ' ' '-'
    printf '|%*s%s%*s|\n' "$LEFT_PADDING" '' "$TITLE" "$RIGHT_PADDING" ''
    printf '+%*s+\n' "$INNER_WIDTH" '' | tr ' ' '-'
}

print_success() {
    echo ""
    echo "Akiba setup complete"
    echo "----------------------------------------"
    echo "Start the project"
    echo "./scripts/server.sh up"
    echo ""
    echo "Thank you for installing Akiba."
    echo "Developer  $DEVELOPER_URL"
    echo "----------------------------------------"
}

if [ ! -f .env ]; then
    step "Preparing Akiba environment file"
    sed -i 's|^VITE_HOST=.*|VITE_HOST=0.0.0.0|' .env
fi

step "Building and starting Akiba containers"
docker compose up --build -d

step "Waiting for the database to be ready"
sleep 15

step "Installing Laravel dependencies"
docker compose exec laravel composer install

step "Installing frontend dependencies"
docker compose exec node npm install

step "Generating application key"
docker compose exec laravel php artisan key:generate

step "Preparing Akiba database"
docker compose exec laravel php artisan migrate
docker compose exec laravel php artisan db:seed

print_success
