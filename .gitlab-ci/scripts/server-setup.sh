#!/bin/bash

sudo ln -s $CI_PROJECT_DIR /var/www/html
/usr/local/bin/frankenphp start --config /etc/caddy/Caddyfile
sudo mkdir -p ./sites/simpletest ./sites/default/files ./build/logs/junit /var/www/.composer
# chown -R www-data:www-data ./sites ./build/logs/junit ./vendor /var/www/
# sudo -u www-data git config --global --add safe.directory $CI_PROJECT_DIR
