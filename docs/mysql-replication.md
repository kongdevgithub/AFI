# MySQL Replication


## MySQL Setup

Grant permission on master:

```
GRANT REPLICATION SLAVE ON *.* TO 'replicate'@'OTHER_SERVER_IP' IDENTIFIED BY 'pass';
GRANT REPLICATION CLIENT ON *.* TO 'replicate'@'OTHER_SERVER_IP';
GRANT SUPER ON *.* TO 'replicate'@'OTHER_SERVER_IP';
GRANT RELOAD ON *.* TO 'replicate'@'OTHER_SERVER_IP';
GRANT SELECT ON *.* TO 'replicate'@'OTHER_SERVER_IP';
GRANT DROP ON *.* TO 'replicate'@'OTHER_SERVER_IP';
GRANT ALTER ON *.* TO 'replicate'@'OTHER_SERVER_IP';
FLUSH PRIVILEGES; 
```

Setup slaves:

```
CHANGE MASTER TO
  MASTER_HOST='OTHER_SERVER_IP',
  MASTER_USER='root',
  MASTER_PASSWORD='root',
  MASTER_PORT=3306,
  MASTER_CONNECT_RETRY=10;
```

Set log file and position:

```
CHANGE MASTER TO MASTER_LOG_FILE = 'bin.000001', MASTER_LOG_POS = 0;
```

Useful commands:

```
STOP SLAVE;
START SLAVE;
SHOW MASTER STATUS;
SHOW SLAVE STATUS \G;
SHOW BINLOG EVENTS;
```


## Firewall Setup

Setup firewall:

```
sudo ufw allow from OTHER_SERVER_IP to any port 3306 proto tcp
```

### Fix for Exposed Docker Ports (Part 1)

Docker modifies iptables directly, see [StackOverflow](https://askubuntu.com/questions/652556/uncomplicated-firewall-ufw-is-not-blocking-anything-when-using-docker).

Add the following to a new file called `/etc/docker/daemon.json`:

```
{"iptables":false}
```

Then restart docker with `service docker restart`.


### Fix for Exposed Docker Ports (Part 2)

Part 1 causes loss of internet connection for the containers, see [StackOverflow](https://stackoverflow.com/questions/17394241/my-firewall-is-blocking-network-connections-from-the-docker-container-to-outside/17498195#17498195).

Edit `/etc/ufw/before.rules` as follows:

In the *filter section, after the first block of required lines, add:

```
# docker rules to enable external network access from the container
# forward traffic accross the bridge 
-A ufw-before-forward -i docker0 -j ACCEPT
-A ufw-before-forward -i testbr0 -j ACCEPT
-A ufw-before-forward -m state --state RELATED,ESTABLISHED -j ACCEPT
```

At the end of the file, after the line that says COMMIT, add the following section:

```
# docker rules to enable external network access from the container
*nat
:POSTROUTING ACCEPT [0:0]
-A POSTROUTING -s 172.16.42.0/8 -o eth0 -j MASQUERADE
COMMIT
```

After saving the file, restart ufw with `sudo ufw disable && sudo ufw enable`.


### Alternative to Part 2 ?


Edit `sudo vi /etc/default/ufw`

```
# Replace:
DEFAULT_FORWARD_POLICY="DROP"
# With:
DEFAULT_FORWARD_POLICY="ACCEPT"
```

Finally, reload the UFW with `sudo ufw reload`.