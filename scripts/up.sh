#!/bin/bash

set -e

line() {
    echo "--------------------------------------"
}

GITHUB_DEV="https://github.com/ojgabrielleal"
GITHUB_REPOSITORY="https://github.com/ojgabrielleal/akiba"

APP_URL="http://localhost:8000"
DOCS_URL="http://localhost:5174"
PHPMYADMIN_URL="http://localhost:8081"

line
echo "Starting containers..."
line

docker compose up -d
clear 

line
echo "Environment is running!"
line
echo "Site: $APP_URL"
echo "Docs: $DOCS_URL"
echo "PHPMyAdmin: $PHPMYADMIN_URL"
line
echo "Github Dev: $GITHUB_DEV"
echo "Github Repository: $GITHUB_REPOSITORY"
line
echo "Panel: $APP_URL/panel"
echo "User: admin"
echo "Pass: admin"
line
