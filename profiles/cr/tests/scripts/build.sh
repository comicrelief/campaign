#!/bin/bash
# Only continue if we are on the "develop" branch
echo $TRAVIS_COMMIT
if [[ $TRAVIS_BRANCH == *"feature/PLAT-675_platenv_develop"* ]]
then

  # Decrypt travis platform.sh ssh key
  openssl aes-256-cbc -K $encrypted_7d6d79e53800_key -iv $encrypted_7d6d79e53800_iv -in travis_rsa.enc -out travis_rsa -d
  # Add platform.sh remote
  eval "$(ssh-agent -s)"
  chmod 600 travis_rsa # this key should have push access
  ssh-add travis_rsa
  git remote add platform git.eu.platform.sh:tx3mbsqmxtu74.git
  ssh -o "StrictHostKeyChecking no" *
  git push platform develop --force
  echo "Pushing to platform.sh develop env."
fi
