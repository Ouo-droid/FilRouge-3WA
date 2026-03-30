#!/bin/bash
set -e

# Activer les extensions pdo_pgsql et pgsql dans php.ini
# Chercher le php.ini utilisé et décommenter les extensions si elles sont commentées
PHP_INI=$(php -r "echo php_ini_loaded_file();")

if [ -n "$PHP_INI" ] && [ -f "$PHP_INI" ]; then
    echo "Modification de $PHP_INI pour activer pdo_pgsql et pgsql..."
    sed -i 's/^;extension=pdo_pgsql/extension=pdo_pgsql/' "$PHP_INI"
    sed -i 's/^;extension=pgsql/extension=pgsql/' "$PHP_INI"
fi

# Vérifier que les extensions sont bien chargées
echo "Extensions PostgreSQL chargées :"
php -m | grep -i pgsql || echo "Extensions installées via docker-php-ext-install"

# Lancer la commande passée en argument (apache2-foreground)
exec "$@"
