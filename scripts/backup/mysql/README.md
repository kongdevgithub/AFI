# MySQL Backup

Backup your MySQL!


## Installation


### Create the Backup Folders

mkdir -p /backup/mysql


### Allow MySQL to Auto-Connect

`vi ~/.my.cnf`

```
[client]
user="backup_user"
pass="the_pass"
```

`chmod 600 ~/.my.cnf`


### Allow Execution

```
chmod a+x /usr/local/linux-backup/mysql/backup.sh
```


### Test the Scripts

```
/usr/local/linux-backup/mysql/backup.sh
```


### Setup Cron Tasks

`crontab -e`

```
0 0 * * * /usr/local/linux-backup/mysql/backup.sh
```


## Restoring Data

single thread:

```
zcat /backup/mysql/YYYY-MM-DD/* | mysql dbname
```

multi thread:

```
echo /backup/mysql/YYYY-MM-DD/*.sql.gz | xargs -n1 -P 16 -I % sh -c 'zcat % | mysql dbname'
```

using myloader:

```
myloader -d /backup/mysql/YYYY-MM-DD/ -B dbname
```


## Nagios Checks


### Client

`vi /etc/nagios/nrpe_local.cfg`

```
command[check_backup_mysql]=/usr/local/linux-backup/mysql/check.php
```

`service nagios-nrpe-server restart`


### Server

`vi /etc/nagios3/conf.d/yourhost.cfg`

```
# check_backup_mysql
define service{
        use                     generic-service
        host_name               yourhost
        service_description     MySQL Backup
        check_command           check_nrpe_1arg!check_backup_mysql
        normal_check_interval   720
        }
```


## Support

- Does this README need improvement?  Go ahead and [suggest a change](https://github.com/cornernote/linux-backup/edit/master/mysql/README.md).
- Found a bug, or need help using this project?  Check the [open issues](https://github.com/cornernote/linux-backup/issues) or [create an issue](https://github.com/cornernote/linux-backup/issues/new).


## License

[BSD-3-Clause](https://raw.github.com/cornernote/linux-backup/master/LICENSE), Copyright © 2013-2014 [Mr PHP](mailto:info@mrphp.com.au)
