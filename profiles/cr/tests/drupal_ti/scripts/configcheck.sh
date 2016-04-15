#!/bin/bash
# Simple script to check code quality.

set -e $DRUPAL_TI_DEBUG

git config --global user.email "travis@example.com"
git config --global user.name "Travis CI"

git status
git stash

phing config:export

git diff --exit-code

git stash apply