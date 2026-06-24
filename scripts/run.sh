#!/bin/bash

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

show_help() {
    echo "Usage:"
    echo "  ./scripts/run.sh install"
    echo "  ./scripts/run.sh up"
    echo "  ./scripts/run.sh down"
    echo "  ./scripts/run.sh artisan ..."
    echo "  ./scripts/run.sh node ..."
    echo "  ./scripts/run.sh composer ..."
    echo ""
}

if [ $# -eq 0 ]; then
    show_help
    exit 0
fi

command="$1"
shift

case "$command" in
    install)
        "$SCRIPT_DIR/shell/install.sh" "$@"
        ;;
    up)
        "$SCRIPT_DIR/shell/up.sh" "$@"
        ;;
    down)
        "$SCRIPT_DIR/shell/down.sh" "$@"
        ;;
    artisan)
        docker compose exec laravel php artisan "$@"
        ;;
    composer)
        docker compose exec laravel composer "$@"
        ;;
    node)
        docker compose exec node npm "$@"
        ;;
    npm)
        docker compose exec node npm "$@"
        ;;
    laravel)
        docker compose exec laravel "$@"
        ;;
    docker)
        docker compose "$@"
        ;;
    *)
        echo "Unknown command: $command"
        echo ""
        show_help
        exit 1
        ;;
esac
