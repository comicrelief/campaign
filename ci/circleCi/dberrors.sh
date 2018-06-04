#!/bin/bash
set -e
# Simple script to check for errors in drupal logs.
cd web
touch tmp.txt
drush wd-show --severity=Emergency > tmp.txt
drush wd-show --severity=Alert > tmp.txt
drush wd-show --severity=Critical > tmp.txt
drush wd-show --severity=Error > tmp.txt
drush wd-show --severity=Warning

FILESIZE=$(cat tmp.txt | wc -c)

if [ $FILESIZE -ne 0 ] ; then
  cat tmp.txt
  rm -rf tmp.txt
  false
fi

rm -rf tmp.txt

true
