# Deployment

## Live Docker Container Updates

```
cd /opt/console
git pull
docker-compose build --no-cache
docker cp src console_php_1:/app
docker cp web console_php_1:/app
docker cp docs console_php_1:/app
docker-compose exec php composer install --optimize-autoloader --prefer-dist
docker-compose exec php yii migrate --interactive=0
docker-compose exec php yii cache/flush-all --interactive=0
docker-compose exec php yii app/clear-assets --interactive=0
sudo docker-compose up -d
```
