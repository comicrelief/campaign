#!/bin/bash
set -e
# Simple script to check code quality.
cd web
touch tmp.txt
drush wd-show --severity=critical > tmp.txt
drush wd-show --severity=error >> tmp.txt
drush wd-show --severity=warning

FILESIZE=$(cat tmp.txt | wc -c)

if [ $FILESIZE -ne 0 ] ; then
  cat tmp.txt
  rm -rf tmp.txt
  exit 0
fi

rm -rf tmp.txt

true
