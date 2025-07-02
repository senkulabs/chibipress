FROM docker.io/serversideup/php:8.3-cli AS vendor

COPY --chown=www-data:www-data . /var/www/html

RUN composer install --no-interaction --optimize-autoloader --ignore-platform-reqs --no-dev
FROM node:20 AS node_modules

RUN mkdir -p /app
WORKDIR /app
COPY . .
COPY --from=vendor /var/www/html /app

RUN npm install
RUN npm run build
FROM docker.io/dunglas/frankenphp:static-builder-musl-1.7.0 AS builder

ENV NO_COMPRESS=1

WORKDIR /go/src/app/dist/app

COPY . .

# Remove tests folder to save space
RUN rm -Rf tests/

COPY --from=vendor /var/www/html /go/src/app/dist/app
COPY --from=node_modules /app/public/build /go/src/app/dist/app/public/build

WORKDIR /go/src/app/

RUN EMBED=dist/app/ ./build-static.sh

FROM docker.io/alpine:3.19.1

# Install dependencies to optimize the uploaded media files
RUN apk add ffmpeg nodejs npm jpegoptim optipng pngquant gifsicle libavif imagemagick ghostscript

RUN npm i -g svgo

ARG build=dev

ENV BUILD $build

WORKDIR /app

COPY --from=builder /go/src/app/dist/frankenphp-linux-x86_64 server
COPY --from=builder /go/src/app/dist/app/entrypoint.sh entrypoint.sh

RUN chmod +x ./entrypoint.sh

ENTRYPOINT [ "./entrypoint.sh" ]
