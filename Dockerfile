FROM php:8.2-apache

RUN rm -f /etc/apache2/mods-enabled/mpm_*.load \
          /etc/apache2/mods-enabled/mpm_*.conf \
    && a2enmod mpm_prefork

COPY . /var/www/html/

# Sauvegarde des données initiales
RUN cp -a /var/www/html/data /var/www/html/data-default

EXPOSE 80

CMD ["bash", "-c", "rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf && a2enmod mpm_prefork && mkdir -p /var/www/html/data && cp -an /var/www/html/data-default/. /var/www/html/data/ && chown -R www-data:www-data /var/www/html/data && chmod -R u+rwX,g+rwX /var/www/html/data && exec apache2-foreground"]
