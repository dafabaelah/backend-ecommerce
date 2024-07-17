# Gunakan base image PHP 8.3 dengan FPM berbasis Alpine
FROM php:8.3-fpm-alpine

# Set working directory
WORKDIR /var/www

# Install dependensi dan ekstensi PHP yang diperlukan
RUN apk --no-cache add \
        nginx \
        supervisor \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        zip \
        libzip-dev \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-install pdo pdo_mysql zip \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

# Copy isi direktori aplikasi yang ada
COPY . /var/www

# Salin konfigurasi Nginx
COPY ./nginx.conf /etc/nginx/nginx.conf
COPY ./default.conf /etc/nginx/conf.d/default.conf

# Pastikan .env tidak ditimpa
COPY ./.env.example /var/www/.env

RUN apk add --no-cache bash

# Ubah kepemilikan direktori aplikasi
RUN chown -R www-data:www-data /var/www

# Ekspos port
EXPOSE 80

# Konfigurasi Supervisor
COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Install dependencies using composer
RUN composer install --no-dev --optimize-autoloader

# Perintah untuk menjalankan Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
