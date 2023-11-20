#!/bin/bash

ln -s $CI_PROJECT_DIR /var/www/html/subdirectory
sudo service apache2 start
[[ $_TARGET_DB == sqlite* ]] && export SIMPLETEST_DB=sqlite://localhost/$CI_PROJECT_DIR/sites/default/files/db.sqlite?module=sqlite
[[ $_TARGET_DB == mysql* ]] && export SIMPLETEST_DB=mysql://$MYSQL_USER:$MYSQL_PASSWORD@database/$MYSQL_DATABASE?module=mysql
[[ $_TARGET_DB == mariadb* ]] && export SIMPLETEST_DB=mysql://$MYSQL_USER:$MYSQL_PASSWORD@database/$MYSQL_DATABASE?module=mysql
[[ $_TARGET_DB == pgsql* ]] && export SIMPLETEST_DB=pgsql://$POSTGRES_USER:$POSTGRES_PASSWORD@database/$POSTGRES_DB?module=pgsql
mkdir -p ./sites/simpletest ./sites/default/files ./build/logs/junit /var/www/.composer
chown -R www-data:www-data ./sites ./build/logs/junit ./vendor /var/www/
sudo -u www-data git config --global --add safe.directory $CI_PROJECT_DIR
