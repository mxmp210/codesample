# Dockerfile for building php environment with required extensions
FROM php:8.2-alpine AS app_php

# Set default environemtn
ENV APP_ENV=prod

# php extensions installer: https://github.com/mlocati/docker-php-extension-installer
COPY --from=mlocati/php-extension-installer:latest --link /usr/bin/install-php-extensions /usr/local/bin/

# Persistent / runtime deps
RUN apk add --no-cache \
        icu-dev \
        libzip \
        zip \
    && rm -rf /var/lib/apt/lists/*;

# Install PHP extensions
RUN set -eux; \
    install-php-extensions \
		intl \
		opcache \
		zip \
        mysqli \
        pdo pdo_mysql  \
        opcache \
    ;

RUN docker-php-ext-enable opcache

# Install XDEBUG - Optional
# RUN pecl install xdebug
# Enable XDEBUG
# RUN docker-php-ext-enable xdebug

# Install composer command
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer
# Set umask to 0000 (newly created files will have 777 permissions)
RUN echo "umask 0000" >> /root/.bashrc

WORKDIR /srv/app

# Run App
CMD ["php", 'server.php']
