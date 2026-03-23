#!/bin/sh
set -e
cd /var/www/html

if [ ! -f vendor/autoload.php ]; then
  echo "laravel-web: instalando dependencias Composer (primera ejecución)..."
  composer install --no-interaction --prefer-dist --optimize-autoloader
fi

mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache

# SQLite en archivo: asegurar que exista si la ruta es relativa al proyecto
if [ "${DB_CONNECTION:-}" = "sqlite" ]; then
  dbpath="${DB_DATABASE:-database/database.sqlite}"
  if [ "$dbpath" != ":memory:" ] && [ -n "$dbpath" ]; then
    case "$dbpath" in
      /*) touchpath="$dbpath" ;;
      *) touchpath="/var/www/html/$dbpath" ;;
    esac
    mkdir -p "$(dirname "$touchpath")"
    [ -f "$touchpath" ] || touch "$touchpath"
  fi
fi

exec php artisan serve --host=0.0.0.0 --port=8080
