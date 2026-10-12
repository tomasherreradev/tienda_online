#!/bin/bash
set -e

cd /var/www/html

php artisan install:velstore --with-import

exec apache2-foreground