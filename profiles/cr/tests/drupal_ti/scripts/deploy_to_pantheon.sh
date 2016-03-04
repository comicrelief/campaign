#!/bin/bash
# Simple script to deploy our `develop` branch to Pantheon continuously.

# Log into Pantheon
terminus auth login "$PEMAIL" --password="$PPASS"

# Change connection mode to Git
terminus site set-connection-mode --site="$PUUID" --env="$PENV" --mode=git

# - git config --global user.email "$_CI_BOT_EMAIL"
# - git config --global user.name "$_CI_BOT_NAME"

# We need to compile CSS
# Then, commit all to the current branch after fixing .gitignore

git remote add pantheon ssh://codeserver.$PENV.$PUUID@codeserver.$PENV.$PUUID.drush.in:2222/~/repository.git

git push pantheon $TRAVIS_BRANCH:master --force

# Change connection mode back to SFTP so we can install
terminus site set-connection-mode --site="$PUUID" --env="$PENV" --mode=sftp

# Install the site
terminus drush --site="$PUUID" --env="$PENV" "site-install --account-pass='$SITEPASS' --site-name='$SITE_NAME $NOW' -y"

# Now, wake up the site
terminus site wake --site="$PUUID" --env="$PENV"
