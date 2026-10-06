# Mykid — PHP 8.3 + Apache image (Coolify / any Docker host)
#
# The app is plain PHP (no database, no Composer, no Node.js). Built-in extensions cover mbstring,
# json, session, hash, pcre and fileinfo; GD (JPEG/PNG/WebP) is added for image resizing.
# Runtime demo data is written to pack-{a,b,c}/storage (falls back to the PHP session if
# not writable). Mount volumes there if the demo data should survive redeploys.

FROM php:8.3-apache

# GD with JPEG / PNG / WebP — used to resize & re-encode uploaded images (max 1600px).
RUN apt-get update \
    && apt-get install -y --no-install-recommends libjpeg62-turbo-dev libpng-dev libwebp-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" gd \
    && rm -rf /var/lib/apt/lists/*

# Production PHP defaults (display_errors off, errors logged) + small hardening.
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && { \
        echo 'expose_php = Off'; \
        echo 'date.timezone = Asia/Bangkok'; \
        echo 'session.cookie_httponly = 1'; \
        echo 'session.use_strict_mode = 1'; \
        echo 'session.cookie_samesite = Lax'; \
        echo 'upload_max_filesize = 12M'; \
        echo 'post_max_size = 64M'; \
        echo 'max_file_uploads = 10'; \
        echo 'memory_limit = 256M'; \
    } > "$PHP_INI_DIR/conf.d/zz-mykid.ini"

# Apache: serve /var/www/html, block internal folders, no rewrite rules needed.
COPY docker/apache/mykid.conf /etc/apache2/conf-available/mykid.conf
RUN a2enmod headers \
    && a2enconf mykid

# Application code (read-only for Apache) + writable demo storage.
COPY . /var/www/html/
RUN mkdir -p /var/www/html/pack-a/storage /var/www/html/pack-b/storage /var/www/html/pack-c/storage \
    && chown -R root:root /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} + \
    && find /var/www/html -type f -exec chmod 644 {} + \
    && chown -R www-data:www-data /var/www/html/pack-a/storage /var/www/html/pack-b/storage /var/www/html/pack-c/storage \
    && chmod 775 /var/www/html/pack-a/storage /var/www/html/pack-b/storage /var/www/html/pack-c/storage

# App mode: "production" turns off developer logs (PHP error_log + browser console).
# Override in Coolify with MYKID_ENV=development when debugging.
ENV MYKID_ENV=production

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -fsS http://localhost/healthz.php > /dev/null || exit 1

# Base image already runs: apache2-foreground
