FROM php:8.2-apache

RUN a2dismod mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork

COPY . /var/www/html/

# Sauvegarde des données initiales pour l'initialisation du volume
RUN cp -a /var/www/html/data /var/www/html/data-default

EXPOSE 80

CMD ["bash", "-c", "mkdir -p /var/www/html/data && cp -an /var/www/html/data-default/. /var/www/html/data/ && exec apache2-foreground"]
