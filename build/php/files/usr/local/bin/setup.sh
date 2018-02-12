#!/usr/bin/env bash

mkdir -p /app/runtime /app/web/assets /app/web/print-spool
chmod -R 775 /app/runtime /app/web/assets /app/web/print-spool
chown -R www-data:www-data /app/runtime /app/web/assets /app/web/print-spool

cd /app
composer install --optimize-autoloader --prefer-dist

/app/yii app/setup-db
