FROM php:8.2-cli-alpine

RUN apk add --no-cache sqlite-libs sqlite-dev \
    && docker-php-ext-install pdo_sqlite \
    && apk del sqlite-dev

WORKDIR /var/www

COPY . .

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public", "public/router.php"]
