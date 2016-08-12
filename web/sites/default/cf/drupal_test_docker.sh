#!/usr/bin/env bash

set -e

. ~/.bashrc
export DB_HOST="campaigntool_mysql_1"
export VCAP_SERVICES="{\"user-provided\":[{\"credentials\":{\"hostname\":\"$DB_HOST\",\"name\":\"drupaldb\",\"password\":\"\",\"username\":\"root\"},\"syslog_drain_url\":\"\",\"label\":\"user-provided\",\"name\":\"service-mysql-1\",\"tags\":[]}]}"
export DB_QUERYSTRING="mysql://root:@$DB_HOST:3306/drupaldb"
export DRUPAL_HASH_SALT="qled1SoV_fRL6cesho4pAjcfGwc5JMqsXbPnwuMKQ1e6HMve5d0SQJ4ukxsF6fHDWDjJlOD_0A"

export DRUPAL_ROOT=$(pwd)
export DRUSH_ALIAS='@self'
export BASE_URL='http://0.0.0.0:80'
export BEHAT_PARAMS='{"extensions":{"Behat\\MinkExtension":{"base_url":"'$BASE_URL'"},"Drupal\\DrupalExtension":{"drupal":{"drupal_root":"'$DRUPAL_ROOT'"},"drush":{"alias":"'$DRUSH_ALIAS'"}}}}'

phing build:prepare
phing test -Dapp.uri="$BASE_URL" -Dapp.dir=$(pwd)
