FROM php:8.0-fpm-alpine

RUN apk add --no-cache git

COPY entrypoint.sh /entrypoint.sh

RUN php --version

ENTRYPOINT ["/entrypoint.sh"]
