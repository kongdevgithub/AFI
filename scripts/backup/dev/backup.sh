#!/usr/bin/env bash

#
# Dev MySQL Backup
#

BACKUPDIR="/backup/mysql-dev/${1}/"
BACKUPFOLDER=`date +"%Y-%m-%d"`
MYDUMPER=`which mydumper`
MKDIR=`which mkdir`

# data backup
${MKDIR} ${BACKUPDIR}${BACKUPFOLDER} -p
echo 'about to run backup command '  ${MYDUMPER} -t 4 -o ${BACKUPDIR}${BACKUPFOLDER} -c -B ${1} -v 3
${MYDUMPER} -t 4 -o ${BACKUPDIR}${BACKUPFOLDER} -c -B ${1} -v 3
