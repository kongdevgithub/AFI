#!/usr/bin/php -q
<?php

################################################################################
# Console Backup Check
################################################################################

// config
$backupPath = '/backup/console/';
$s3Bucket = 's3://afibranding-backup/' . exec('hostname') . '/console';
$weeklyBackupDay = 'sunday';
$s3cmd = '/usr/local/bin/s3cmd -c /var/lib/nagios/.s3cfg';
$dailyBackupFile = '/backup/console.tgz';
$weeklyPrefix = 'console-';

// defines
define('OK', 0);
define('WARNING', 1);
define('CRITICAL', 2);
define('UNKNOWN', 3);

// checks
$errors = $warnings = array();

// check file count
$count = count(glob($backupPath . '*'));
if (!$count) {
    $errors[] = 'backup has no files';
}

// check daily file in s3
$s3File = $s3Bucket . '/' . basename($dailyBackupFile);
ob_start();
system($s3cmd . ' ls --list-md5 ' . $s3File);
$s3List = explode("\n", trim(ob_get_clean()));
if (!$s3List) {
    $warnings[] = 's3 daily file foes not exist';
} else {
    // compare local file to s3
    $s3File = $s3List[0];
    $s3File = explode(' ', preg_replace('/\s+/', ' ', $s3File));
    if (filesize($dailyBackupFile) != $s3File[2]) { // compare filesize
        $warnings[] = $s3File[4] . ' filesize does not match s3 (' . $s3File[2] . ')';
    } elseif (md5_file($dailyBackupFile) != $s3File[3]) { // compare md5
        $warnings[] = $s3File[4] . ' hash does not match s3 (' . $s3File[3] . ')';
    }
}

// check weekly files in s3
$s3WeeklyFile = $s3Bucket . '/weekly/' . $weeklyPrefix . date('Y-m-d', strtotime('last ' . $weeklyBackupDay)) . '.tgz';
ob_start();
system($s3cmd . ' ls ' . $s3WeeklyFile);
$s3Weekly = trim(ob_get_clean());
if (!$s3Weekly) {
    $warnings[] = 's3 weekly backup does not exist at ' . $s3WeeklyFile;
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
