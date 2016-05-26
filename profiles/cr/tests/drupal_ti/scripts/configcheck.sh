#!/bin/bash
# Simple script to check code quality.

set -e $DRUPAL_TI_DEBUG

# We need to setup git so we can git stash
git config --global user.email "travis@example.com"
git config --global user.name "Travis CI"

# Re-export all config - this should not show any changes!
phing config:export

chmod 777 sites/default/settings.php

# mv sites/default/settings.php settings.php.tmp
sudo git checkout sites/default/settings.php

git status


# Git diff - will exit if there is any difference after running config:export
# Excludes settings.php as that has been modified by the installer
echo "git diff excluding settings.php"
git status -s |grep -v ^\ D |grep -v sites/default/settings.php |cut -b4- >> git-diff.txt
cat git-diff.txt

# mv sites/default/settings.php.tmp sites/default/settings.php

FILESIZE=$(cat git-diff.txt | wc -c)

if [ $FILESIZE -ne 0 ] ; then
  git diff
  rm -rf git-diff.txt
  exit 1
fi

cat git-diff.txt
rm -rf git-diff.txt

echo "Reached end of file"
