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
git diff --exit-code `git status -s |grep -v ^\ D |grep -v sites/default/settings.php |cut -b4-`