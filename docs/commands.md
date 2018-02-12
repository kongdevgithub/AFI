# Commands

## Scheduled Tasks

Task scheduling is done using the linux cron system.

Configure Cron tasks by running `sudo crontab -e`:

```
MAILTO="webmaster@afirbanding.com.au"
YII=/var/www/console/yii
LOCK="/usr/local/bin/lockrun --idempotent --lockfile=/var/www/console/runtime/lockrun."
LOG=/var/www/console/logs/

# m h  dom mon dow   command
* * * * * ${LOCK}test -- ${YII} test > /dev/null 2>&1
```

The crontab is backed up using the backup/console/backup.sh script. You can restore the crontab as follows

```
# from docker install
rsync -azv -e ssh afi:/backup/console/var/spool/cron/crontabs/root ~/my-crontab
crontab ~/my-crontab
rm -f ~/my-crontab

# or from S3 backup
s3cmd get s3://afibranding-backup/host2.afi.ink/console/console.tgz ~/
cd ~
tar xvfz console.tgz
crontab ~/backup/console/var/spool/cron/crontabs/root
rm -rf ~/console.tgz ~/console/
```


## Overlap Protection

Install `lockrun` for overlap protection:

```
wget https://raw.githubusercontent.com/cornernote/lockrun/master/lockrun.c
sudo apt-get install gcc
gcc lockrun.c -o lockrun
sudo cp lockrun /usr/local/bin/
sudo apt-get remove gcc
sudo apt-get autoremove
```


## Yii2 Commands

Console commands can be run by executing `yii` in the application folder.

For example, the following will return a list of available commands:

```
cd /var/www/console
./yii
```

Create a command by adding the following to `src/commands/CustomController.php`:

```php
<?php
namespace app\commands;
use yii\console\Controller;
class CustomController extends Controller
{
    public function actionIndex()
    {
        $this->stdout('HELLO WORLD' . "\n");
    }
}
```

Run the command:

```
./yii custom/index
```