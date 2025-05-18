FROM php:8.1-cli

RUN docker-php-ext-install sockets pdo pdo_mysql

RUN ln -sf /usr/share/zoneinfo/Europe/Rome /etc/localtime && \
    echo "Europe/Rome" > /etc/timezone

WORKDIR /app
COPY ./files/ ./

CMD ["php", "ws.php"]