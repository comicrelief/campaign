#!/bin/bash
# Simple script to check code quality.

set -e $DRUPAL_TI_DEBUG

git status
git stash

phing config:export

git diff --exit-code

git stash apply