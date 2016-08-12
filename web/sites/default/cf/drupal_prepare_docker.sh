#!/usr/bin/env bash

set -e

. ~/.bashrc
export DB_HOST="campaigntool_mysql_1"
export VCAP_SERVICES="{\"user-provided\":[{\"credentials\":{\"hostname\":\"$DB_HOST\",\"name\":\"drupaldb\",\"password\":\"\",\"username\":\"root\"},\"syslog_drain_url\":\"\",\"label\":\"user-provided\",\"name\":\"service-mysql-1\",\"tags\":[]}]}"
export DB_QUERYSTRING="mysql://root:@$DB_HOST:3306/drupaldb"
export DRUPAL_HASH_SALT="qled1SoV_fRL6cesho4pAjcfGwc5JMqsXbPnwuMKQ1e6HMve5d0SQJ4ukxsF6fHDWDjJlOD_0A"

phing -Ddrush.bin=drush -Ddb.querystring="$DB_QUERYSTRING" build:prepare
phing -Ddrush.bin=drush -Ddb.querystring="$DB_QUERYSTRING" build
phing -Ddrush.bin=drush -Ddb.querystring="$DB_QUERYSTRING" cron
