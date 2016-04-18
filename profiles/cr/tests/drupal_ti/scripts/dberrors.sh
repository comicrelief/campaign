#!/bin/bash
# Simple script to check code quality.

set -e $DRUPAL_TI_DEBUG

drush wd-show --severity=critical > tmp.txt
drush wd-show --severity=warning >> tmp.txt
drush wd-show --severity=error >> tmp.txt

cat tmp.txt
rm tmp.txt
