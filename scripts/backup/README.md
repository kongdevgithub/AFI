# Linux Backup

- [S3 Configutation](#s3-configutation)
	- [Create AWS S3 Bucket](#create-aws-s3-bucket)
	- [Create AWS IAM User](#create-aws-iam-user)
	- [Install s3cmd](#install-s3cmd)
	- [Setup S3 Endpoints](#setup-s3-endpoints)
- [Nagios Configutation](#nagios-configutation)
	- [Configure s3cmd](#configure-s3cmd)
	- [Create AWS IAM User](#create-aws-iam-user-1)
	- [Extend Nagios Timeout](#extend-nagios-timeout)
	- [Testing Nagios Commands](#testing-nagios-commands)
- [Support](#support)
- [License](#license)
	 
	
## S3 Configutation


### Create AWS S3 Bucket

Login to [AWS](https://console.aws.amazon.com)

Click **Services**, then click **S3**.

Click **Create Bucket**.

Enter a **Bucket Name** and choose the **Region**, then click **Create**.


### Create AWS IAM User

Login to [AWS](https://console.aws.amazon.com)

Click the **username dropdown** (top right), then click **Security Credentials**.

Click **Users** (left menu).

Click **Create New Users**.

Enter a **Username** then click **Create**.

Save your access keys somewhere safe.

Click the user, then click **Permissions** (tab in the bottom pane), then click **Attach User Policy**.

Click **Custom Policy**, then click **Select**.

Enter a **Policy Name** (can be the same as the username), then paste in the following into the **Policy Document** to give access to all s3 buckets.

```
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": ["s3:*"],
      "Resource": "*"
    }
  ]
}
```

Note: this gives full permissions to all S3 buckets, which is required when verifying the s3cmd configuration (see steps below).

After s3cmd has been configured, you may want to change the permissions to restrict to a single bucket.

Click the user, then click **Permissions** (tab in the bottom pane), then click **Manage Policy**.

Paste the following into the **Policy Document**, then click **Apply Policy**.

```
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": ["s3:*"],
      "Resource": "arn:aws:s3:::bucket-name"
    },
    {
      "Effect": "Allow",
      "Action": ["s3:*"],
      "Resource": "arn:aws:s3:::bucket-name/*"
    }
  ]
}
```

Thanks to Junda Ong for his [blog post](http://samwize.com/2013/04/21/s3cmd-broken-pipe-error-errno-32/) and [stackoverflow answer](http://stackoverflow.com/a/16128347/599477) explaining how to set the correct policy.


### Install s3cmd

Download and configure s3cmd:

```
apt-get install python-setuptools
wget https://github.com/s3tools/s3cmd/releases/download/v1.6.1/s3cmd-1.6.1.tar.gz
tar xvfz s3cmd-1.6.1.tar.gz
cd s3cmd-1.6.1/
python setup.py install
s3cmd --configure
```

Symlink s3cmd to prevent issues when running from cron

```
ln -s /usr/local/bin/s3cmd /usr/bin/s3cmd
```


### Setup S3 Endpoints

This step is optional, however it may reduce initial connection time.

`vi ~/.s3cfg`

```
host_base = s3-ap-southeast-2.amazonaws.com
host_bucket = %(bucket)s.s3-ap-southeast-2.amazonaws.com
```

Sydney uses `s3-ap-southeast-2`, or check [other region endpoints](http://docs.aws.amazon.com/general/latest/gr/rande.html).


## Nagios Configutation


### Configure s3cmd

The nagios user will not have access to your `~/.s3cmd`, so you will need to copy it to the nagios home folder.

```
cp ~/.s3cfg /var/lib/nagios/
chown nagios:nagios /var/lib/nagios/.s3cfg
```


### Create AWS IAM User

It is **highly** recommended to setup a read-only AWS IAM user and insert the credentials into `/var/lib/nagios/.s3cfg` instead of using your backup user.  Use the following AWS Policy Document to allow readonly access:

```
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": ["s3:Get*","s3:List*"],
      "Resource": "arn:aws:s3:::bucket-name"
    },
    {
      "Effect": "Allow",
      "Action": ["s3:Get*","s3:List*"],
      "Resource": "arn:aws:s3:::bucket-name/*"
    }
  ]
}
```


### Extend Nagios Timeout

It is recommended to extend the NRPE timeout to avoid this error:

```
CHECK_NRPE: Socket timeout after 10 seconds.
```

#### Server

Add to your nagios server `/etc/nagios3/commands.cfg`:

```
define command{
        command_name    check_nrpe_1arg_300sec
        command_line    $USER1$/check_nrpe -H $HOSTADDRESS$ -c $ARG1$ -t 300
        }
```        

Then replace `check_nrpe_1arg` with `check_nrpe_1arg_60sec` in your nagios checks.

Visit [this article](http://deadlockprocess.wordpress.com/2010/07/11/how-to-fix-service-check-time-outs-in-nagios-nrpe-deployed-in-centosrhel-5/) or the [NRPE Documentation](http://nagios.sourceforge.net/docs/nrpe/NRPE.pdf) for more information on nagios timeouts.

#### Client

Update the NRPE config `/etc/nagios/nrpe.cfg`:

```
command_timeout=300
connection_timeout=600
```

### Testing Nagios Commands

Test the commands by running on the nagios client as the nagios user:

```
sudo -u nagios /usr/local/linux-backup/assets/check.php
```

Test the commands from the nagios server:

```
/usr/lib/nagios/plugins/check_nrpe -H hostname -c check_backup_assets -t 60
```

If you are still having trouble and need to debug the check.php script, you may find it useful to [see stderr from php](http://stackoverflow.com/questions/2320608/php-stderr-after-exec).


## Support

- Does this README need improvement?  Go ahead and [suggest a change](https://github.com/cornernote/linux-backup/edit/master/README.md).
- Found a bug, or need help using this project?  Check the [open issues](https://github.com/cornernote/linux-backup/issues) or [create an issue](https://github.com/cornernote/linux-backup/issues/new).


## License

[BSD-3-Clause](https://raw.github.com/cornernote/linux-backup/master/LICENSE), Copyright © 2013-2014 [Mr PHP](mailto:info@mrphp.com.au)
