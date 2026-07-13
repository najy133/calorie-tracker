#!/bin/sh
set -e

# Railway assigns a random $PORT and terminates TLS at its edge, forwarding
# plain HTTP to the container. FrankenPHP's default Caddyfile listens on
# $SERVER_NAME; ":<port>" (no host) means plain HTTP on that port — exactly
# what we want behind Railway's proxy.
export SERVER_NAME=":${PORT:-8080}"

# SQLite lives on a mounted volume so data survives redeploys. The file may not
# exist on a brand-new volume, so make sure it (and its directory) are there
# before migrating.
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ] && [ -n "${DB_DATABASE:-}" ]; then
    mkdir -p "$(dirname "$DB_DATABASE")"
    [ -f "$DB_DATABASE" ] || touch "$DB_DATABASE"
fi

# Cache config/routes/views at boot — this runs with the real runtime env, so
# config:cache captures the actual secrets rather than build-time blanks.
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Apply pending migrations on every deploy. --force is required to run
# non-interactively in production.
php artisan migrate --force

# Expose the public storage symlink (harmless if it already exists).
php artisan storage:link 2>/dev/null || true

exec "$@"
