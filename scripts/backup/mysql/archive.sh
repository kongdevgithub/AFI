#!/usr/bin/env bash

################################################################################
# MySQL Archive
################################################################################

SOURCE=h=127.0.0.1,D=console_audit
LIMIT=10000

function data_archive {
    echo "BEGIN ARCHIVE ${1}"
    pt-archiver \
        --source ${SOURCE},t=${1} \
        --where "created < '`date +"%Y-%m-%d 00:00:00" --date="-${2}"`'" \
        --limit ${LIMIT} \
        --progress ${LIMIT} \
        --commit-each \
        --no-check-charset \
        --purge
    echo "DONE ARCHIVE ${1}"
    echo ""
}

data_archive audit_data 90days
data_archive audit_error 90days
data_archive audit_javascript 90days
data_archive audit_mail 365days
data_archive audit_trail 365days
data_archive audit_entry 366days