#!/bin/sh
set -e

ROOT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")/../.." && pwd)"
cd "$ROOT_DIR"

APP_URL="http://localhost:8000"
PANEL_URL="http://localhost:8000/panel"
PHPMYADMIN_URL="http://localhost:8080" 
DEVELOPER_URL="https://github.com/ojgabrielleal"

print_links() {
    echo ""
    echo "Akiba is Ready!!"
    echo "----------------------------------------"
    echo "Site       $APP_URL"
    echo "Panel      $PANEL_URL"
    echo "PHPMyAdmin $PHPMYADMIN_URL"
    echo ""
    echo "Admin panel credentials"
    echo "User       admin"
    echo "Password   admin"
    echo ""
    echo "Available scripts"
    echo "./scripts/run.sh server up        Start project"
    echo "./scripts/run.sh server down      Stop project"
    echo "./scripts/run.sh server restart   Restart project"
    echo "./scripts/run.sh laravel <cmd>    Run command in Laravel container"
    echo "./scripts/run.sh node <cmd>       Run command in Node container"
    echo "./scripts/run.sh composer <cmd>   Run command in composer"
    echo "./scripts/run.sh shell [service]  Open shell in a service"
    echo ""
    echo "Examples"
    echo "./scripts/run.sh laravel php artisan migrate"
    echo "./scripts/run.sh node npm install"
    echo "./scripts/run.sh composer require vendor/package"
    echo "./scripts/run.sh shell node"
    echo ""
    echo "Thank you for using Akiba."
    echo "Developer  $DEVELOPER_URL"
    echo "----------------------------------------"
}

stop_process() {
    SERVICE="$1"
    PID_FILE="$2"
    NAME="$3"

    docker compose exec "$SERVICE" sh -c "
        if [ -f '$PID_FILE' ]; then
            PID=\$(cat '$PID_FILE')
            if kill \"\$PID\" 2>/dev/null; then
                echo 'Stopped $NAME'
            fi
            rm -f '$PID_FILE'
        fi
    "
}

start_laravel() {
    stop_process laravel storage/logs/artisan-serve.pid "Laravel server"

    docker compose exec -d laravel sh -c "
        php artisan serve --host=0.0.0.0 --port=8000 > storage/logs/artisan-serve.log 2>&1 &
        echo \$! > storage/logs/artisan-serve.pid
        wait \$!
    "
}

start_node() {
    stop_process node storage/logs/vite.pid "Vite server"

    docker compose exec -d node sh -c "
        npm run dev -- --host 0.0.0.0 --port 5173 > storage/logs/vite.log 2>&1 &
        echo \$! > storage/logs/vite.pid
        wait \$!
    "
}

case "${1:-up}" in
    up)
        echo "Starting Docker containers"
        docker compose up -d

        echo "Starting Laravel server"
        start_laravel

        echo "Starting Vite server"
        start_node

        print_links
        ;;

    down)
        echo "Stopping Docker containers"
        docker compose down
        rm -f storage/logs/artisan-serve.pid storage/logs/vite.pid
        ;;

    restart)
        "$0" down
        "$0" up
        ;;

    *)
        echo "Usage: $0 [up|down|restart]"
        exit 1
        ;;
esac
