#!/bin/bash
# Simple script to check code quality.

set -e $DRUPAL_TI_DEBUG

# We need to setup git so we can git stash
git config --global user.email "travis@example.com"
git config --global user.name "Travis CI"

~/.composer/vendor/bin/drush.php pml

git status

# Stash our changes to settings.php and possibly any other files
chmod 777 sites/default/settings.php
chmod -R 777 sites/default
# cp sites/default/settings.php sites/default/settings.tmp.php
# git stash

ls -l sites/default

git status
# cp sites/default/settings.tmp.php sites/default/settings.php

# Re-export all config - this should not show any changes!
phing config:export

echo "GIT diffing exluding settings.php"
git diff `git status -s |grep -v ^\ D |grep -v sites/default/settings.php |cut -b4-`


cp sites/default/settings.php sites/default/settings.tmp.php

git checkout sites/default/settings.php

# Exit if there is a diff

echo "GIT diffing with ALL1"

git diff --exit-code


cp sites/default/settings.tmp.php sites/default/settings.php


# Re-apply our changes to settings.php
git stash apply







phing login