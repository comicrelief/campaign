#!/bin/bash
# Simple script to check code quality.

set -e $DRUPAL_TI_DEBUG

drush wd-show --severity=critical > tmp.txt
drush wd-show --severity=warning >> tmp.txt
drush wd-show --severity=error >> tmp.txt

FILESIZE=$(cat tmp.txt | wc -c)

if [ $FILESIZE -ne 0 ] ; then
  cat tmp.txt
  rm -rf tmp.txt
  exit 1
fi

rm -rf tmp.txt
