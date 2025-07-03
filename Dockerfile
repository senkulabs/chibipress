FROM docker.io/serversideup/php:8.3-cli AS vendor

COPY --chown=www-data:www-data . /var/www/html

RUN composer install --no-interaction --optimize-autoloader --ignore-platform-reqs --no-dev
FROM node:20 AS node_modules

RUN mkdir -p /app
WORKDIR /app
COPY . .
COPY --from=vendor /var/www/html/vendor /app

RUN npm install
RUN npm run build
FROM docker.io/dunglas/frankenphp:static-builder-musl-1.7.0 AS builder

ENV NO_COMPRESS=1

WORKDIR /go/src/app/dist/app

COPY . .

# Remove tests folder to save space
RUN rm -Rf tests/
RUN rm -Rf archive/

COPY --from=vendor /var/www/html/vendor vendor
COPY --from=vendor /var/www/html/composer.lock composer.lock
COPY --from=node_modules /app/public/build public/build

WORKDIR /go/src/app/

RUN EMBED=dist/app/ ./build-static.sh

# Latest releases available at https://github.com/aptible/supercronic/releases
ENV SUPERCRONIC_URL=https://github.com/aptible/supercronic/releases/download/v0.2.34/supercronic-linux-amd64 \
    SUPERCRONIC=supercronic-linux-amd64 \
    SUPERCRONIC_SHA1SUM=e8631edc1775000d119b70fd40339a7238eece14

RUN curl -fsSLO "$SUPERCRONIC_URL" \
    && echo "${SUPERCRONIC_SHA1SUM}  ${SUPERCRONIC}" | sha1sum -c - \
    && chmod +x "$SUPERCRONIC" \
    && mv "$SUPERCRONIC" "/usr/local/bin/supercronic"

FROM docker.io/alpine:3.19.1

# Install dependencies to optimize the uploaded media files
RUN apk add ffmpeg nodejs npm jpegoptim optipng pngquant gifsicle libavif imagemagick ghostscript

RUN npm i -g svgo

ARG build=dev

ENV BUILD $build

WORKDIR /app

COPY crontab crontab

COPY --from=builder /go/src/app/dist/frankenphp-linux-x86_64 server
COPY --from=builder /go/src/app/dist/app/entrypoint.sh entrypoint.sh
COPY --from=builder --chmod=755 /go/src/app/dist/app/supervisord.conf /etc/supervisord.conf
COPY --from=builder /usr/local/bin/supercronic /usr/local/bin/supercronic
COPY --from=docker.io/ochinchina/supervisord:latest /usr/local/bin/supervisord /usr/local/bin/supervisord

RUN chmod +x ./entrypoint.sh

ENTRYPOINT [ "./entrypoint.sh" ]
