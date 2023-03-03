FROM php:7.4.33-apache
RUN apt-get update && \
     apt-get install -y \
         libzip-dev \
         && docker-php-ext-install zip
RUN docker-php-ext-install pdo pdo_mysql
RUN chown -R www-data:www-data /var/www/html/
EXPOSE 80
