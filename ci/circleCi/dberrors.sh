#!/bin/bash
set -e
# Simple script to check code quality.
cd web
touch tmp.txt
drush wd-show --severity=0 > tmp.txt
drush wd-show --severity=1 > tmp.txt
drush wd-show --severity=2 > tmp.txt
drush wd-show --severity=3 > tmp.txt
drush wd-show --severity=4 > tmp.txt

FILESIZE=$(cat tmp.txt | wc -c)

if [ $FILESIZE -ne 0 ] ; then
  cat tmp.txt
  rm -rf tmp.txt
  false
fi

rm -rf tmp.txt

true
