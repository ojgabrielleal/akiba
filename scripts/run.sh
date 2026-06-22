#!/bin/sh
set -e

SCRIPT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)"
COMMAND="${1:-help}"

usage() {
    echo "Usage: ./scripts/run.sh <command> [args]"
    echo ""
    echo "Commands: install, server, laravel, node, composer, shell"
    echo ""
    echo "Examples:"
    echo "./scripts/run.sh install"
    echo "./scripts/run.sh server up"
    echo "./scripts/run.sh laravel php artisan migrate"
    echo "./scripts/run.sh node npm install"
    echo "./scripts/run.sh shell node"
}

case "$COMMAND" in
    help|--help|-h)
        usage
        exit 0
        ;;
    install|server|laravel|node|composer|shell)
        shift
        exec "$SCRIPT_DIR/shell/$COMMAND.sh" "$@"
        ;;
    *)
        echo "Unknown Akiba command: $COMMAND" >&2
        usage >&2
        exit 1
        ;;
esac
