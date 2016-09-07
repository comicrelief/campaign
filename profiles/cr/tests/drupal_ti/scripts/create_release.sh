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

  git branch -a --list > branches

  # Check if we already have a release branch in RND17, if not create a new one
  if grep -q $TRAVIS_BRANCH branches
  then
    echo "Release branch already exists in RND17. We'll ping this branch so it triggers a rebuild."

    body='{
    "request": {
      "branch":"'
    body+=$TRAVIS_BRANCH
    body+='"}}'
    # @todo remove this and move it to Travis CI config
    token='Vk5ddW7b3fvSguMDzGddaQ'

    curl -s -X POST \
      -H "Content-Type: application/json" \
      -H "Accept: application/json" \
      -H "Travis-API-Version: 3" \
      -H "Authorization: token $token" \
      -d "$body" \
      https://api.travis-ci.com/repo/comicrelief%2Frnd17/requests
  else
    echo "Release branch created in RND17. We'll push this up so that a build is triggered."
    git checkout -b $TRAVIS_BRANCH
    git push origin $TRAVIS_BRANCH
  fi

  # Cleanup
  rm branches
  rm -fr rnd17
fi

