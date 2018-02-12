#!/usr/bin/env bash

echo '##################################################'
echo '# Server Update                                  #'
echo '##################################################'
echo ''

echo 'RUNNING: git pull'
git pull
echo ''

echo 'RUNNING: composer install --ignore-platform-reqs --optimize-autoloader --prefer-dist'
composer install --ignore-platform-reqs --optimize-autoloader --prefer-dist
echo ''

echo 'RUNNING: ./yii migrate --interactive=0'
../yii migrate --interactive=0
echo ''

echo 'RUNNING ./yii cache/flush-all'
../yii cache/flush-all --interactive=0
echo ''

echo 'RUNNING ./yii app/clear-assets'
../yii app/clear-assets --interactive=0
echo ''

echo 'RUNNING: service supervisor restart'
service supervisor restart
echo ''

echo 'DONE!'
echo ''