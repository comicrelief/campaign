#!/bin/bash
# Only continue if we are on the "develop" branch
echo $TRAVIS_COMMIT
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
  
  git config user.name "Travis CI"
  git config user.email "travis-ci@comicrelief.com"

  cd ../
  git clone git@github.com:comicrelief/rnd17.git --branch develop --single-branch
  cd rnd17
  git checkout -b $TRAVIS_BRANCH
  sed -i -e "/branch:/ s/: .*/: $TRAVIS_BRANCH/" profiles/rnd17/rnd17.make.yml
  git add .
  git commit -v -m 'Commit '






fi
