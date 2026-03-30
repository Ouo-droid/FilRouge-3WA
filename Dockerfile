FROM php:8.2-apache

# Forcer mpm_prefork (seul MPM compatible avec mod_php) et activer les modules nécessaires
RUN rm -f /etc/apache2/mods-enabled/mpm_event.* \
           /etc/apache2/mods-enabled/mpm_worker.* \
    && a2enmod mpm_prefork rewrite headers

# Installer les dépendances système pour PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev && rm -rf /var/lib/apt/lists/*

# Installer les extensions PHP PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql pgsql

# Installer et activer Xdebug pour le debug pas a pas dans VS Code
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Charger la configuration Xdebug
COPY docker/php/conf.d/xdebug.ini /usr/local/etc/php/conf.d/99-xdebug.ini

# Copier la configuration Apache (DocumentRoot → public/)
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Copier le code source de l'application
COPY . /var/www/html/

# Donner les droits à www-data sur les fichiers
RUN chown -R www-data:www-data /var/www/html

# Copier et rendre exécutable l'entrypoint
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
