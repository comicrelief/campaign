#!/usr/bin/env bash

set -ex

[ -d campaign ] && cd campaign
[ -d ~/.bashrc ] && . ~/.bashrc

phing build:prepare
phing build -Ddrush.bin=drush -Ddb.querystring="$DB_QUERYSTRING"
phing cron -Ddrush.bin=drush
phing cache:clear -Ddrush.bin=drush
