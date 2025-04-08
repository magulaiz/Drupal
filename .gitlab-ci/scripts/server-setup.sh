#!/bin/bash

# Path to FrankenPHP binary
FRANKENPHP_BIN="/usr/local/bin/frankenphp"

# FrankenPHP parameters
FRANKENPHP_ARGS="php-server -a"

# User and group to run the service
USER="www-data"
GROUP="www-data"

PIDFILE="/var/run/frankenphp.pid"

ln -s $CI_PROJECT_DIR /var/www/html/subdirectory
#sudo service apache2 start
sudo start-stop-daemon --start --quiet --background --pidfile $PIDFILE --make-pidfile --chuid $USER:$GROUP --exec $FRANKENPHP_BIN -- $FRANKENPHP_ARGS
mkdir -p ./sites/simpletest ./sites/default/files ./build/logs/junit /var/www/.composer
chown -R www-data:www-data ./sites ./build/logs/junit ./vendor /var/www/
sudo -u www-data git config --global --add safe.directory $CI_PROJECT_DIR
