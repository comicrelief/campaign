#!/bin/bash
# Only continue if we are on the "develop" branch
if [[ $TRAVIS_BRANCH == *"develop"* ]]
then

  # Decrypt travis platform.sh ssh key
  openssl aes-256-cbc -K $encrypted_7d6d79e53800_key -iv $encrypted_7d6d79e53800_iv -in travis_rsa.enc -out travis_rsa -d
  # Add platform.sh remote
  eval "$(ssh-agent -s)"
  chmod 600 travis_rsa # this key should have push access
  ssh-add travis_rsa
  ssh-keyscan -H git.eu.platform.sh >> ~/.ssh/known_hosts
  git remote add platform tx3mbsqmxtu74@git.eu.platform.sh:tx3mbsqmxtu74.git
  git push platform develop --force
  echo "Pushing to platform.sh develop env."
fi

if [[ $TRAVIS_BRANCH == *"#integrate"* ]]
then
  ls -la
  cd ../
  echo 'Clone RND17 Repo.'
  git clone git@github.com:comicrelief/rnd17.git --branch feature/RND-593_remove_core_contrib --single-branch
  cd rnd17
  # Configure project
  cp ../campaign/build.properties .
  echo 'File: build.properties copied over from campaign.'
  cp ../campaign/sites/default/environment.yml sites/default/
  echo 'File: environment.yml copied over from campaign.'
  # Prepare project directory and get dependencies
  phing build:prepare
  # Git config
  git config user.name "Travis CI"
  git config user.email "travis-ci@comicrelief.com"
  # Update CR profile version in makefile
  sed -i -e "/branch:/ s/: .*/: $(echo $TRAVIS_BRANCH | sed -e 's/\\/\\\\/g; s/\//\\\//g; s/&/\\\&/g')/" rnd17/rnd17.make.yml
  echo 'File: profiles/rnd17/rnd17.make.yml has been updated with current branch.'
  # Create new branch in RND named the same as current feature/branch
  git checkout -b $TRAVIS_BRANCH
  git commit -va -m 'Update campaign profile version'
  # Update campaign profile code from feature branch
  phing make-cr
  git add --all
  git commit -va -m 'File changes via `phing make-cr`'
  phing update-cr
  # Add all config changes and commit
  git add --all
  git commit -va -m 'Update configuration via `phing update-cr`'
  git push origin HEAD --force
fi
