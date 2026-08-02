#!/bin/bash

set -e

line() {
    echo "--------------------------------------"
}

GITHUB_DEV="https://github.com/ojgabrielleal"
GITHUB_REPOSITORY="https://github.com/ojgabrielleal/akiba"

APP_URL="http://localhost:8000/"
DB_HOST="mysql"
DB_USERNAME="root"
DB_PASSWORD="root"

line
echo "Preparing .env file..."
line

if [ ! -f ".env" ]; then
    cp .env.example .env
fi

sed -i \
    -e "s|^APP_URL=.*|APP_URL=$APP_URL|" \
    -e "s|^DB_HOST=.*|DB_HOST=$DB_HOST|" \
    -e "s|^DB_USERNAME=.*|DB_USERNAME=$DB_USERNAME|" \
    -e "s|^DB_PASSWORD=.*|DB_PASSWORD=$DB_PASSWORD|" \
    .env
clear

line
echo "Building Docker environment..."
line

docker compose build
docker compose up -d
clear 

line
echo "Installing PHP dependencies..."
line

docker compose exec app composer install
clear

line
echo "Generating Laravel app key..."
line

docker compose exec app php artisan key:generate
clear

line
echo "Installing Node dependencies..."
line

docker compose exec node npm install
clear

line
echo "Running database migrations..."
line

docker compose exec app php artisan migrate:fresh --seed
clear

line
echo "Create storage link..."
line

docker compose exec app php artisan storage:link --force
docker compose down
clear

line
echo "Environment configured successfully!"
line
echo "To start the environment, run:"
echo " ./run.sh up"
line
echo "Github Dev: $GITHUB_DEV"
echo "Github Repository: $GITHUB_REPOSITORY"
line
