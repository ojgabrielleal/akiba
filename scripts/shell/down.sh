#!/bin/bash

set -e

line() {
    echo "--------------------------------------"
}

line
echo "Stopping containers..."
line

docker compose down

line
echo "Environment stopped successfully!"
line
