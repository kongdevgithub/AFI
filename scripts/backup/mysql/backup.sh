#!/usr/bin/env bash

################################################################################
# MySQL Backup
################################################################################

# config
BACKUPDIR=/backup/mysql/
BACKUPNAME=`date +"%Y-%m-%d"`
BACKUPDAYS=14
S3BUCKET=s3://afibranding-backup/`hostname`/mysql
FULLBACKUPDAY=Sun

# binary paths
MYSQLDUMP=`which mysqldump`
MYDUMPER=`which mydumper`
FIND=`which find`
S3CMD=`which s3cmd`
MKDIR=`which mkdir`

# dump mysql databases
${MKDIR} -p ${BACKUPDIR}${BACKUPNAME}
${MYDUMPER} -o ${BACKUPDIR}${BACKUPNAME} -c # -t 4

# delete old backups
${FIND} ${BACKUPDIR} -mtime +${BACKUPDAYS} -delete
${FIND} ${BACKUPDIR} -type d -empty -delete

# upload changed files to s3
${S3CMD} sync --recursive --delete-removed ${BACKUPDIR}${BACKUPNAME}/ ${S3BUCKET}/daily/

# check if we do a full remote backup today
if [[ `date '+%a'` == ${FULLBACKUPDAY} ]]; then

	# upload full backup to s3
	${S3CMD} put --recursive ${BACKUPDIR}${BACKUPNAME} ${S3BUCKET}/weekly/

fi
