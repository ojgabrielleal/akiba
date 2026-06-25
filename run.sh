#!/bin/bash

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

command="$1"
shift

case "$command" in
    install)
        "$SCRIPT_DIR/scripts/install.sh" "$@"
        ;;
    up)
        "$SCRIPT_DIR/scripts/up.sh" "$@"
        ;;
    down)
        docker compose down "$@"
        ;;
    artisan)
        docker compose exec laravel php artisan "$@"
        ;;
    composer)
        docker compose exec laravel composer "$@"
        ;;
    node)
        docker compose exec node "$@"
        ;;
    npm)
        docker compose exec node npm "$@"
        ;;
    *)
        echo "Unknown command: $command"
        echo ""
        help
        exit 1
        ;;
esac
