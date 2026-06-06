#!/bin/sh
set -e

ROOT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"
cd "$ROOT_DIR"

SERVICE="${1:-laravel}"

docker compose exec "$SERVICE" sh
