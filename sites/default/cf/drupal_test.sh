#!/usr/bin/env bash

set -e

[ -d campaign ] && cd campaign
[ -d ~/.bashrc ] && . ~/.bashrc

export DRUPAL_ROOT=$(pwd)
export DRUSH_ALIAS='@self'
export BEHAT_PARAMS='{"extensions":{"Behat\\MinkExtension":{"base_url":"'$BASE_URL'"},"Drupal\\DrupalExtension":{"drupal":{"drupal_root":"'$DRUPAL_ROOT'"},"drush":{"alias":"'$DRUSH_ALIAS'"}}}}'

phing build:prepare
phing test -Dapp.uri="$BASE_URL" -Dapp.dir=$(pwd)
