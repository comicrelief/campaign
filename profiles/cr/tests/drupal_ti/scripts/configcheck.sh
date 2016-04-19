#!/bin/bash
# Simple script to check code quality.

set -e $DRUPAL_TI_DEBUG

git config --global user.email "travis@example.com"
git config --global user.name "Travis CI"
chmod 777 sites/default/settings.php

git status
git stash

phing config:export

git diff --exit-code

git stash apply