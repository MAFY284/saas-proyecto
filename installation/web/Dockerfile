ARG NODE_VERSION=22

FROM node:${NODE_VERSION}-bookworm AS node

FROM php:8.4-apache

ARG UID=1000
ARG GID=1000
ARG NODE_VERSION=22

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    curl \
    ca-certificates \
    gosu \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mysqli \
        mbstring \
        zip \
        gd \
        intl \
        bcmath \
        opcache \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Node.js desde stage oficial (Node 22 LTS — npm/Vite/Tailwind)
COPY --from=node /usr/local/bin/node /usr/local/bin/node
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN set -eux; \
    ln -sf /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm; \
    ln -sf /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx; \
    node -v; \
    npm -v; \
    MAJOR="$(node -p "process.versions.node.split('.')[0]")"; \
    test "$MAJOR" = "$NODE_VERSION"

# Alinea www-data con el UID/GID del host (editar ../project sin sudo)
RUN groupmod -o -g "${GID}" www-data \
    && usermod -o -u "${UID}" www-data

COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
RUN a2ensite 000-default

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www/html

EXPOSE 80 5173
ENTRYPOINT ["docker-entrypoint.sh"]
