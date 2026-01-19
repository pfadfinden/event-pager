#syntax=docker/dockerfile:1

# =============================================================================
# Worker Base Image
# =============================================================================
# Shared base for all worker containers with PHP CLI, s6-overlay, and extensions
FROM php:8.5-cli-alpine AS worker_base

# Add php extension installer
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Install s6-overlay
ADD https://github.com/just-containers/s6-overlay/releases/download/v3.1.5.0/s6-overlay-noarch.tar.xz /tmp/
ADD https://github.com/just-containers/s6-overlay/releases/download/v3.1.5.0/s6-overlay-x86_64.tar.xz /tmp/

RUN tar -C / -Jxpf /tmp/s6-overlay-noarch.tar.xz && \
    tar -C / -Jxpf /tmp/s6-overlay-x86_64.tar.xz && \
    rm -f /tmp/s6-overlay-*.tar.xz

# Install PHP extensions
RUN set -eux; \
    install-php-extensions \
        @composer \
        intl \
        opcache \
        zip \
        pdo_pgsql \
        pcntl \
        redis \
    ;

# Create non-root user for privilege dropping
ARG APP_UID=1000
ARG APP_GID=1000

RUN addgroup -g ${APP_GID} appgroup && \
    adduser -D -u ${APP_UID} -G appgroup appuser

WORKDIR /app

# Environment for s6-overlay privilege dropping + read-only FS
ENV S6_OVERLAY_DROP_PRIVS=true \
    S6_UID=1000 \
    S6_GID=1000 \
    S6_READ_ONLY_ROOT=1 \
    COMPOSER_ALLOW_SUPERUSER=1

# =============================================================================
# Production App Layer (shared)
# =============================================================================
# Contains app code and composer dependencies - shared by all prod workers
FROM worker_base AS worker_app_prod

# Install composer dependencies first (better layer caching)
COPY --link composer.* symfony.* ./
RUN set -eux; \
    composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

# Copy source code (excluding build artifacts and assets)
COPY --link --exclude=build/ --exclude=dist/ . ./

# Finalize composer autoloader and run post-install scripts
RUN set -eux; \
    mkdir -p var/cache var/log; \
    composer dump-autoload --classmap-authoritative --no-dev; \
    composer dump-env prod; \
    chmod +x bin/console; sync

# =============================================================================
# Development Targets
# =============================================================================
# Dev workers expect source code via volume mount

FROM worker_base AS worker_dev

COPY ./build/worker/services.d /etc/services.d

RUN find /etc/services.d -type f -name "run" -exec chmod +x {} \; && \
    find /etc/services.d -type f -name "finish" -exec chmod +x {} \;

ENTRYPOINT ["/init"]


FROM worker_base AS worker_intelpage_dev

COPY ./build/worker-intelpage/services.d /etc/services.d

RUN find /etc/services.d -type f -name "run" -exec chmod +x {} \; && \
    find /etc/services.d -type f -name "finish" -exec chmod +x {} \;

ENTRYPOINT ["/init"]

# =============================================================================
# Production Targets
# =============================================================================
# Prod workers have app code baked in from worker_app_prod

FROM worker_app_prod AS worker_prod

COPY ./build/worker/services.d /etc/services.d

RUN find /etc/services.d -type f -name "run" -exec chmod +x {} \; && \
    find /etc/services.d -type f -name "finish" -exec chmod +x {} \;

ENTRYPOINT ["/init"]


FROM worker_app_prod AS worker_intelpage_prod

COPY ./build/worker-intelpage/services.d /etc/services.d

RUN find /etc/services.d -type f -name "run" -exec chmod +x {} \; && \
    find /etc/services.d -type f -name "finish" -exec chmod +x {} \;

ENTRYPOINT ["/init"]
