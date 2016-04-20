#!/bin/bash
# Simple script to check code quality.

set -e $DRUPAL_TI_DEBUG

# We need to setup git so we can git stash
git config --global user.email "travis@example.com"
git config --global user.name "Travis CI"

~/.composer/vendor/bin/drush.php pml

git status

# Stash our changes to settings.php
chmod 777 sites/default/settings.php
cp sites/default/settings.php sites/default/settings.tmp.php
chmod -R 777 sites/default
git stash

git status

# Re-export all config - this should not show any changes!
phing config:export

# Exit if there is a diff
git diff --exit-code

# Re-apply our changes to settings.php
git stash apply







phing login