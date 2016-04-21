#!/bin/bash
# Simple script to check code quality.

set -e $DRUPAL_TI_DEBUG

# We need to setup git so we can git stash
git config --global user.email "travis@example.com"
git config --global user.name "Travis CI"

# Re-export all config - this should not show any changes!
phing config:export

# Git diff - will exit if there is any difference after running config:export
# Excludes settings.php as that has been modified by the installer
echo "GIT diff excluding settings.php"
git diff `git status -s |grep -v ^\ D |grep -v sites/default/settings.php |cut -b4-` >> git-diff.txt
cat git-diff.txt

FILESIZE=$(cat git-diff.txt | wc -c)

if [ $FILESIZE -ne 0 ] ; then
  cat git-diff.txt
  rm -rf git-diff.txt
  exit 1
fi

rm -rf git-diff.txt