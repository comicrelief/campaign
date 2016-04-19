#!/bin/bash
# Simple script to check code quality.

set -e $DRUPAL_TI_DEBUG

drush wd-show --severity=critical > tmp.txt
drush wd-show --severity=warning >> tmp.txt
drush wd-show --severity=error >> tmp.txt

FILESIZE=(wc -c < "tmp.txt")
echo $FILESIZE

if [ $FILESIZE -ne 0 ]
  then
    rm -rf tmp.txt
    exit 1
fi

rm -rf tmp.txt
