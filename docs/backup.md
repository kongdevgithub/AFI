# Server Backup

The following backup commands are run daily:

```
# crontab
# m h  dom mon dow   command
0 0 * * * /scripts/linux-backup/mysql/backup.sh
0 1 * * * /scripts/linux-backup/console/backup.sh
0 2 * * * ${LOCKRUN}mysql-archive -- /scripts/linux-backup/mysql/archive.sh
0 23 * * * ${LOCKRUN}mysql-audit-cleanup -- /home/console/jobflw4/scripts/mysql_audit_cleanup.sh
```

## MySQL Backup

Every day the entire mysql database is backed up.

It is stored locally, and also sent to AmazonS3 `s3://afibranding-backup/console.afibranding.com.au/mysql/`.

Retention:

* Locally 14 days using `mysqldumper`
* AmazonS3 the data is stored in a single daily folder, and each week is archived to a new weekly folder for 30 days, then sent to glacier for 365 days.



## Console Backup

Every day the data in `/home/console/` is backed up.

It is stored locally, and also sent to AmazonS3 `s3://afibranding-backup/console.afibranding.com.au/console/`.

Retention:

* Locally 14 days using `rdiff-backup`
* AmazonS3 the data is stored in a single daily folder, and each week is archived to a new weekly folder for 30 days, then sent to glacier for 365 days.


## Setup s3cmd

Download and configure s3cmd:

```
sudo apt-get install python-setuptools
wget https://github.com/s3tools/s3cmd/releases/download/v1.6.1/s3cmd-1.6.1.tar.gz
tar xvfz s3cmd-1.6.1.tar.gz
cd s3cmd-1.6.1/
sudo python setup.py install
s3cmd --configure
```

Symlink s3cmd to prevent issues when running from cron

```
ln -s /usr/local/bin/s3cmd /usr/bin/s3cmd
```
