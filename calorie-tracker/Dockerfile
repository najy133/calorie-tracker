# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# Stage 1 — build the front-end assets (Tailwind v4 + Vite)
# ---------------------------------------------------------------------------
FROM node:20-alpine AS assets
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

# ---------------------------------------------------------------------------
# Stage 2 — PHP runtime (FrankenPHP bundles a production web server + PHP 8.4)
# ---------------------------------------------------------------------------
FROM dunglas/frankenphp:1-php8.4 AS app
WORKDIR /app

# Extensions Laravel needs: sqlite driver, intl (locale/dates), zip, opcache.
RUN install-php-extensions pdo_sqlite intl zip opcache pcntl

# Composer binary (copied from the official image — no global install needed).
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install PHP dependencies first, on their own layer, so they only re-resolve
# when composer.json/lock change — keeps rebuilds fast.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# Application source.
COPY . .

# Compiled assets from stage 1 (public/build holds the Vite manifest).
COPY --from=assets /app/public/build ./public/build

# Optimised autoloader + package discovery now that the full source is present.
RUN composer dump-autoload --no-dev --optimize \
    && php artisan package:discover --ansi || true

# Laravel needs these writable at runtime.
RUN chown -R www-data:www-data storage bootstrap/cache

# Sensible production defaults (Railway env vars still override these).
ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr

COPY docker-entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

ENTRYPOINT ["entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
