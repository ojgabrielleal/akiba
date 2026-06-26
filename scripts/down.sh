#!/bin/bash

set -e

line() {
    echo "--------------------------------------"
}

GITHUB_DEV="https://github.com/ojgabrielleal"
GITHUB_REPOSITORY="https://github.com/ojgabrielleal/akiba"

line
echo "Stopping containers..."
line

docker compose down
clear

line
echo "Environment stopped!"
line
echo "Containers stopped successfully."
echo "Database volume was preserved."
line
echo "Github Dev: $GITHUB_DEV"
echo "Github Repository: $GITHUB_REPOSITORY"
line
