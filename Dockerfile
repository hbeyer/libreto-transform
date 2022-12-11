FROM php:7.4.33-apache
RUN apt-get update && \
     apt-get install -y \
         libzip-dev \
         && docker-php-ext-install zip
COPY ./ /var/www/html
EXPOSE 80