#!/bin/bash
# Simple script to check code quality.

set -e $DRUPAL_TI_DEBUG

drush wd-show --severity=critical > tmp.txt
drush wd-show --severity=warning >> tmp.txt
drush wd-show --severity=error >> tmp.txt

FILESIZE=$(cat tmp.txt | wc -c)
echo $FILESIZE

if $FILESIZE -ne 0; then
  rm -rf tmp.txt
  echo "entra"
  exit 1
else
  echo "sale"
fi

rm -rf tmp.txt
