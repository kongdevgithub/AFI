# Docker Compose

See: [docker-compose](https://docs.docker.com/compose/install/)

Build containers:

```
docker-compose build
docker-compose build --force-rm # Always remove intermediate containers.
docker-compose build --no-cache # Do not use cache when building the image.
docker-compose pull             # Pull latest source images
```

Run containers:

```
docker-compose up -d
```

Stop containers:

```
docker-compose down -v # remove volumes
```

View logs:

```
docker-compose logs -f [container]
[container] = php | nginx | db
```

Run bash prompt:

```
docker-compose exec [container] bash     # is it's already running
docker-compose run --rm [container] bash # to start it up
[container] = php | nginx | db
```

Bash shortcut (allow running `yii` when you are in `cd /opt/console`):

```
echo alias yii=\'/usr/local/bin/docker-compose exec php yii\' >> ~/.bashrc && source ~/.bashrc
```

List containers:

```
docker-compose ps
```

Destroy all docker containers, images and volumes:

```
docker stop $(docker ps -a -q)
docker rm $(docker ps -a -q)
docker rmi $(docker images -q)
docker volume prune -f
docker system prune -f -a --volumes
```

Cleanup dangling volumes:

```
docker volume rm $(docker volume ls -qf dangling=true)
```

Stop services conflicting with ports

```
sudo service mysql stop
sudo service gearman-job-server stop
sudo service apache2 stop
sudo service memcached stop
```

Disable services

```
sudo systemctl disable mysql.service
sudo systemctl disable gearman-job-server.service
sudo systemctl disable apache2.service
sudo systemctl disable memcached.service
```