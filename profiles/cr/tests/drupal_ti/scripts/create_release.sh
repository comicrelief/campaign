#!/bin/bash
# Simple script to automate creation of a release branch for RND17

set -e $DRUPAL_TI_DEBUG

# Only continue if we are on a release branch
if [[ "$TRAVIS_BRANCH" = "release"* ]]
then
  echo "We are on a release branch. Prepare integration into RND17"

  # Check out a new branch or find existing release branch?
  git clone git@github.com:comicrelief/rnd17.git rnd17
  cd rnd17

  echo "PRINT branch list"
  git branch -a --list

  # Check if we already have a release branch in RND17, if not create a new one
  if [ `git branch -a --list remotes/origin/$TRAVIS_BRANCH `]
  then
    echo "Release branch already exists in RND17. We'll ping this branch so it triggers a rebuild."
    git checkout $TRAVIS_BRANCH
  else
    echo "Release branch created in RND17. We'll push this up so that a build is triggered."
    git checkout -b $TRAVIS_BRANCH
    git push origin $TRAVIS_BRANCH
  fi
fi

