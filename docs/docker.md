# Docker

Start with a fresh Ubuntu 16.04. 


## Download Codebase

**PROD**: Generate a key pair, then add your public key to the [BitBucket Project](https://bitbucket.org/afibranding/console/admin/access-keys/).

```
ssh-keygen
cat ~/.ssh/id_rsa.pub
```

Clone this repository (see [Git](git.md)).

```
cd /opt
git clone git@bitbucket.org:afibranding/console.git
cd console/
git remote add https https://bitbucket.org/afibranding/console.git
```


## Config Setup/Transfer

**DEV**: Copy the default config files

```
cp docker-compose.override.yml-dist docker-compose.override.yml
cp src/app.env-dist src/app.env
```

**PROD**: Transfer config files from live server

```
# from docker install
rsync -azv -e ssh afi:/backup/console/opt/console/docker-compose.override.yml /opt/console/
rsync -azv -e ssh afi:/backup/console/opt/console/src/app.env /opt/console/src/

# from ubuntu 14.04 install (old)
rsync -azv -e ssh afi:/home/console/jobflw4/docker-compose.override.yml /opt/console/
rsync -azv -e ssh afi:/home/console/jobflw4/src/app.env /opt/console/src/

# or from S3 backup
s3cmd get s3://afibranding-backup/host2.afi.ink/console/console.tgz /restore/
```


## Docker Setup

Install docker and docker-compose

```
sudo wget -qO- https://goo.gl/hukjGs | sh
```

Start the docker containers

```
sudo docker-compose up -d
```

**PROD**: Initialise the volumes from the host volume (live server only, first time only)

```
docker cp src console_php_1:/app
docker cp web console_php_1:/app
docker cp docs console_php_1:/app
```

Initialise the container (first time only)

```
docker-compose exec php setup.sh
```


## MySQL Data Transfer

Install required packages on host machine:

```
apt-get install -y mysql-client mydumper
```

Add to `~/.my.cnf` (change password for **PROD**)

```
[client] 
host=127.0.0.1
user=root
password=root
```

### Get Data

Create folders to store restored mysql data

```
sudo mkdir -p ~/restore/mysql
cd ~/restore/mysql
```

**DEV**: Download from trello upload

```
wget https://trello-attachments.s3.amazonaws.com/594b882471b89065b1662b6c/594b890a570c9ea1811a4d47/2b3cfd40769507741bc85d8fe5362d8e/console.tar.gz
tar xvfz console.tar.gz && mv console/* . && rm -rf console.tar.gz console/
```

**PROD**: Download from another live server

```
# from docker install
rsync -azv -e ssh afi:/backup/mysql/`date +"%Y-%m-%d"`/metadata ~/restore/mysql
rsync -azv --progress \
    -e ssh afi:/backup/mysql/`date +"%Y-%m-%d"`/console* ~/restore/mysql \
    --exclude console.session.sql.gz \
    --exclude console_data.log.sql.gz \
    --exclude console_audit.audit_data.sql.gz \
    --exclude console_audit.audit_entry.sql.gz \
    --exclude console_audit.audit_error.sql.gz \
    --exclude console_audit.audit_javascript.sql.gz \
    --exclude console_audit.audit_mail.sql.gz \
    --exclude console_audit.audit_trail.sql.gz

# from ubuntu 14.04 install  (old)
rsync -azv -e ssh afi:/backup/mysql/`date +"%Y-%m-%d"`/metadata ~/restore/mysql
rsync -azv --progress \
    -e ssh afi:/backup/mysql/`date +"%Y-%m-%d"`/console* ~/restore/mysql \
    --exclude console.session.sql.gz \
    --exclude console_data.log.sql.gz \
    --exclude console_audit.audit_data.sql.gz \
    --exclude console_audit.audit_entry.sql.gz \
    --exclude console_audit.audit_error.sql.gz \
    --exclude console_audit.audit_javascript.sql.gz \
    --exclude console_audit.audit_mail.sql.gz \
    --exclude console_audit.audit_trail.sql.gz

# or download from S3
s3cmd get s3://afibranding-backup/host.afi.ink/mysql/daily/metadata ~/restore/mysql
s3cmd get s3://afibranding-backup/host.afi.ink/mysql/daily/console* ~/restore/mysql

# or download from S3 (old server)
s3cmd get s3://afibranding-backup/console.afibranding.com.au/mysql/daily/metadata ~/restore/mysql
s3cmd get s3://afibranding-backup/console.afibranding.com.au/mysql/daily/console* ~/restore/mysql
```


### Load Data

Load the database backups into mysql (first setup `~/.my.cnf`)

```
myloader -d ~/restore/mysql/ -v 3
myloader -d ~/restore/mysql/ -v 3 -o # overwrite
```
