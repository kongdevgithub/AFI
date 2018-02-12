# Assets Backup

Backup your Assets!


## Installation


### Create the Backup Folders

```
mkdir -p /backup/assets
```


### Allow Execution

```
chmod a+x /usr/local/linux-backup/assets/backup.sh
```


### Configuration

Change settings in `/usr/local/linux-backup/assets/backup.sh`


### Test the Scripts

```
/usr/local/linux-backup/assets/backup.sh
```


### Setup Cron Tasks

`crontab -e`

```
0 1 * * * /usr/local/linux-backup/assets/backup.sh
```


## Restoring Data

```
rdiff-backup --force --restore-as-of "2013-10-05T00:00:00" /backup/assets/ /backup/restore/
```


## Nagios Checks


### Client

Add the command to `/etc/nagios/nrpe_local.cfg`:

```
command[check_backup_asset]=/usr/local/linux-backup/assets/check.php
```

Then restart nagios:

```
service nagios-nrpe-server restart
```

Set permissions on the rdiff-backup-data folder so nagios can read the increments:

```
chmod 755 /backup/assets/rdiff-backup-data
chmod 644 /backup/assets/rdiff-backup-data/chars_to_quote
```


### Server

`vi /etc/nagios3/conf.d/yourhost.cfg`

```
# check_backup_assets
define service{
        use                     generic-service
        host_name               yourhost
        service_description     Assets Backup
        check_command           check_nrpe_1arg!check_backup_assets
        normal_check_interval   720
        }
```


## Support

- Does this README need improvement?  Go ahead and [suggest a change](https://github.com/cornernote/linux-backup/edit/master/assets/README.md).
- Found a bug, or need help using this project?  Check the [open issues](https://github.com/cornernote/linux-backup/issues) or [create an issue](https://github.com/cornernote/linux-backup/issues/new).


## License

[BSD-3-Clause](https://raw.github.com/cornernote/linux-backup/master/LICENSE), Copyright © 2013-2014 [Mr PHP](mailto:info@mrphp.com.au)
