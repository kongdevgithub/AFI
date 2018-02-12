# Security

## Firewall

Configure UFW firewall:

```
sudo apt-get install -y ufw
ufw default deny incoming
ufw default allow outgoing
ufw allow 56322/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable

# others
ufw disable
ufw status
ufw reset # delete all rules
ufw delete allow 22/tcp # delete a rule
```

## SSH Authentication

Allow auto login to SSH with your public key:

```
mkdir ~/.ssh
cat > ~/.ssh/authorized_keys
* Paste the public key here, Then press ctrl-D *
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys
```


## SSH Security

Secure SSH bu running `sudo nano /etc/ssh/sshd_config`, then change the following:

```
PermitRootLogin no
PasswordAuthentication no
```

Then restart SSH:

```
service ssh restart
```

Auto access SSH server by running `nano ~/.ssh/config`, then add the following:

```
Host afi host.afi.ink
	HostName host.afi.ink
	Port 56322
	IdentityFile ~/.ssh/mrphp_2013_rsa
	User mrphp
```

## System Users

Create user:

```
sudo adduser YOURUSER
sudo mkdir /home/YOURUSER/.ssh
```

Run `sudo nano /home/YOURUSER/.ssh/authorized_keys`, then paste in your public key.

Add your user to sudoers list by running `visudo`, then add this to the bottom of the file:

```
YOURUSER ALL=(ALL) ALL
```


## MySQL Users

Add user to MySQL:

```
mysql> GRANT ALL ON *.* TO 'YOURUSER'@'localhost' IDENTIFIED BY 'YOURPASS';
```

Auto-access MySQL by running `nano ~/.my.cnf`, then add the following:

```
[client]
host=127.0.0.1
user=YOURUSER
password=YOURPASS
```

Then set permissions:

```
chmod 600 ~/.my.cnf
```
