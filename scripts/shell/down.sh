#!/bin/bash

set -e

line() {
    echo "--------------------------------------"
}

clear

line
echo "Stopping containers..."
line

docker compose down
clear

line
echo "Environment stopped successfully!"
line
