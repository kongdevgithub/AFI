#!/usr/bin/env bash

################################################################################
# Console Backup
################################################################################

# config
DESTINATION=/backup/console
BACKUPDAYS=14
S3BUCKET=s3://afibranding-backup/`hostname`/console
FULLBACKUPDAY=Sun
DAILYNAME=/backup/console.tgz
WEEKLYNAME=${S3BUCKET}/weekly/console-`date +"%Y-%m-%d"`.tgz

# binary paths
MKDIR=`which mkdir`
DOCKER=`which docker`
TAR=`which tar`
FIND=`which find`
S3CMD=`which s3cmd`
RM=/bin/rm
CP=/bin/cp


#########################################
# BEGIN: backup config files            #
#########################################

# app
${MKDIR} -p ${DESTINATION}/opt/console/src
${CP} /opt/console/docker-compose.override.yml ${DESTINATION}/opt/console/docker-compose.override.yml
${DOCKER} cp console_php_1:/app/src/app.env ${DESTINATION}/opt/console/src/app.env

# crontab
${MKDIR} -p ${DESTINATION}/var/spool/cron/crontabs
${CP} /var/spool/cron/crontabs/root ${DESTINATION}/var/spool/cron/crontabs/root

# nagios
${MKDIR} -p ${DESTINATION}/etc/nagios
${CP} /etc/nagios/nrpe_local.cfg ${DESTINATION}/etc/nagios/nrpe_local.cfg

# certs
${MKDIR} -p ${DESTINATION}/etc/letsencrypt/live/afi.ink
${CP} /etc/letsencrypt/live/afi.ink/fullchain.pem ${DESTINATION}/etc/letsencrypt/live/afi.ink/fullchain.pem
${CP} /etc/letsencrypt/live/afi.ink/privkey.pem ${DESTINATION}/etc/letsencrypt/live/afi.ink/privkey.pem

#########################################
# END: backup config files              #
#########################################


# delete compressed backup
${RM} -f ${DAILYNAME}

# compress latest daily backup
${TAR} cfzP ${DAILYNAME} ${DESTINATION}/

# upload changed files to s3
${S3CMD} put ${DAILYNAME} ${S3BUCKET}/

# check if we do a full remote backup today
if [[ `date '+%a'` == ${FULLBACKUPDAY} ]]; then

	# upload compressed backup to s3
	${S3CMD} put ${DAILYNAME} ${WEEKLYNAME}

fi
