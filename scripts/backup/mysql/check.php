#!/usr/bin/php -q
<?php

################################################################################
# MySQL Backup Check
################################################################################

// config
$backupPath = '/backup/mysql/';
$s3Bucket = 's3://afibranding-backup/' . exec('hostname') . '/mysql';
$weeklyBackupDay = 'sunday';
$s3cmd = '/usr/local/bin/s3cmd -c /var/lib/nagios/.s3cfg';

// defines
define('OK', 0);
define('WARNING', 1);
define('CRITICAL', 2);
define('UNKNOWN', 3);

// checks
$errors = $warnings = array();

// check daily file count
$dailyPath = $backupPath . date('Y-m-d') . '/';
$count = count(glob($dailyPath . '*'));
if (!$count) {
    $errors[] = 'backup has no files in ' . $dailyPath;
}

// check file count in s3
ob_start();
$s3BucketDaily = $s3Bucket . '/daily/';
system($s3cmd . ' ls --list-md5 ' . $s3BucketDaily);
$s3List = explode("\n", trim(ob_get_clean()));
if (count($s3List) != $count) {
    $warnings[] = 's3 daily count does not match local count';
}
else {
    // compare local file to s3
    foreach ($s3List as $s3File) {
        $s3File = explode(' ', preg_replace('/\s+/', ' ', $s3File));
        $localFilename = $dailyPath . substr($s3File[4], strlen($s3BucketDaily));
        // compare filesize
        if (filesize($localFilename) != $s3File[2]) {
            $warnings[] = $s3File[4] . ' filesize does not match s3 (' . $s3File[2] . ')';
        }
        // compare md5
        if (md5_file($localFilename) != $s3File[3]) {
            $warnings[] = $s3File[4] . ' hash does not match s3 (' . $s3File[3] . ')';
        }
    }
}

// check weekly files in s3
$weeklyPath = $s3Bucket . '/weekly/' . date('Y-m-d', strtotime('last ' . $weeklyBackupDay)) . '/';
ob_start();
system($s3cmd . ' ls ' . $weeklyPath . ' | wc -l');
$s3CountWeekly = ob_get_clean();
if ($s3CountWeekly < 10) {
    $warnings[] = 's3 weekly backup has ' . count($s3CountWeekly) . ' files in ' . $weeklyPath;
}

// some errors
if ($errors) {
    echo 'CRITICAL: ' . implode(', ', $errors);
    die(CRITICAL);
}

// some warnings
if ($warnings) {
    echo 'WARNING: ' . implode(', ', $warnings);
    die(WARNING);
}

// checks pass, sweet!
echo 'OK: files:' . $count;
die(OK);
