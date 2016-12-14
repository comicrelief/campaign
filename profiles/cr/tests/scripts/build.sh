#!/bin/bash
# Only continue if we are on the "develop" branch
echo $TRAVIS_COMMIT
if [[ $TRAVIS_BRANCH == *"feature/PLAT-675_platenv_develop"* ]]
then
  echo "Pushing to platform.sh develop env."
  # Add platform.sh remote
  git remote add platform git.eu.platform.sh:tx3mbsqmxtu74.git
  git push platform develop --force
fi
