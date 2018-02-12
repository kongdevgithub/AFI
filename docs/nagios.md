# Nagios

## Client Monitoring

Install packages

```
sudo apt-get install -y nagios-nrpe-server nagios-plugins
```

Service config `vi /etc/nagios/nrpe.cfg`

```
allowed_hosts=127.0.0.1,162.243.80.151
```

Restart service

```
service nagios-nrpe-server restart
```

Check if it's running

```
tail -f /var/log/syslog | grep nrpe
```

Firewall

```
sudo ufw allow from 162.243.80.151 to any port 5666 proto tcp
```


Run `visudo` and add to the bottom

```
User_Alias NRPERS = nagios, nrpe
Cmnd_Alias NRPERSCOMMANDS = /usr/bin/docker inspect *, /usr/lib/nagios/plugins/check_disk *, /opt/console/scripts/nagios/check_docker_container.sh *, /opt/console/scripts/nagios/check_memcached.sh *
Defaults:NRPERS !requiretty
NRPERS ALL=(root) NOPASSWD: NRPERSCOMMANDS

# old
#nagios    ALL=(ALL:ALL)  NOPASSWD: /usr/bin/docker inspect *
#nagios    ALL=(ALL:ALL)  NOPASSWD: /usr/lib64/nagios/plugins/check-docker-container.sh *
```

