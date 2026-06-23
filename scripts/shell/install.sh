#!/bin/bash

set -e

line() {
    echo "--------------------------------------"
}

line
echo "Preparing .env file..."
line

if [ ! -f ".env" ]; then
    cp .env.example .env
fi

if [ ! -f ".env.testing" ]; then
    cp .env.testing.example .env.testing
fi

APP_URL="http://localhost:8000/"
DB_HOST="mysql"
DB_USERNAME="root"
DB_PASSWORD="root"

sed -i \
    -e "s|^APP_URL=.*|APP_URL=$APP_URL|" \
    -e "s|^DB_HOST=.*|DB_HOST=$DB_HOST|" \
    -e "s|^DB_USERNAME=.*|DB_USERNAME=$DB_USERNAME|" \
    -e "s|^DB_PASSWORD=.*|DB_PASSWORD=$DB_PASSWORD|" \
    .env

line
echo "Building Docker environment..."
line

docker compose build

line
echo "Starting containers..."
line

docker compose up -d

line
echo "Installing PHP dependencies..."
line

docker compose exec laravel composer install

line
echo "Generating Laravel app key..."
line

docker compose exec laravel php artisan key:generate

line
echo "Installing Node dependencies..."
line

docker compose exec node npm install

line
echo "Running database migrations..."
line

docker compose exec laravel php artisan migrate:fresh --seed
docker compose down

line
echo "Environment configured successfully!"
line
echo "Containers were stopped after installation."
echo "To start the environment, run:"
echo "  ./scripts/run.sh up"
echo "Github Repository: https://github.com/ojgabrielleal/akiba"
line
