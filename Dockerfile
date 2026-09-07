FROM php:8.2-apache

RUN a2dismod mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork

COPY . /var/www/html/

EXPOSE 80
