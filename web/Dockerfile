FROM php:8.2-apache

RUN a2enmod rewrite

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN ln -sf /usr/share/zoneinfo/Europe/Rome /etc/localtime && \
    echo "Europe/Rome" > /etc/timezone

COPY apache.conf /etc/apache2/sites-available/000-default.conf

COPY ./files/ /var/www/html/

WORKDIR /var/www/html/