FROM mysql:8.0

ENV MYSQL_ROOT_PASSWORD=rootpass \
    MYSQL_DATABASE=chat_app \
    MYSQL_USER=chatuser \
    MYSQL_PASSWORD=chatpass

COPY init.sql /docker-entrypoint-initdb.d/

RUN ln -sf /usr/share/zoneinfo/Europe/Rome /etc/localtime && \
    echo "Europe/Rome" > /etc/timezone
    
EXPOSE 3306