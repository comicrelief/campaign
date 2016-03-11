#!/bin/bash
# Simple script to deploy our `develop` branch to Pantheon continuously.

"export TRAVIS_COMMIT_MSG=\"$(git log --format=%B --no-merges -n 1)\""
echo "$TRAVIS_COMMIT_MSG" | grep '\[pantheon deploy\]'; export PANTHEON_DEPLOY=$?; true

echo $TRAVIS_COMMIT_MSG
echo $PANTHEON_DEPLOY

# Only continue if we are on the "develop" branch
echo $TRAVIS_BRANCH
if [ "$TRAVIS_BRANCH" = "develop" ]
then
    # For Pantheon, add a private SSH key (see https://github.com/pantheon-systems/travis-scripts)
  	openssl aes-256-cbc -K $encrypted_f913de0c14f1_key -iv $encrypted_f913de0c14f1_iv -in travis-ci-key.enc -out ~/.ssh/id_rsa -d
  	chmod 0600 ~/.ssh/id_rsa

	# Include Terminus in the path so it can be found
  	export PATH="$TRAVIS_BUILD_DIR/profiles/cr/tests/behat/vendor/bin:$PATH"

	# Dynamic hosts through Pantheon mean constantly checking interactively
	# that we mean to connect to an unknown host. We ignore those here.
	echo "StrictHostKeyChecking no" > ~/.ssh/config

	# Log into Pantheon
	terminus auth login "$PEMAIL" --password="$PPASS"

	# We need to compile CSS
	# Then, commit all to the current branch after fixing .gitignore
	git config --global user.email "$CI_BOT_EMAIL"
	git config --global user.name "$CI_BOT_NAME"

	# Remove .gitignore so we can commit CSS/JS
	rm .gitignore

	# Now commit css/js dir
	cd "$DRUPAL_TI_THEME_DIR"
	git add css
	git add js
	cd "$TRAVIS_BUILD_DIR"

	# Show git status just for debugging
	git status

	export CI_COMMIT_MSG="Branch $TRAVIS_BRANCH compiled CSS"
	git commit -a -m "Built by CI - $CI_COMMIT_MSG"

	# Add a new remote for Pantheon
	git remote add pantheon ssh://codeserver.$PENV.$PUUID@codeserver.$PENV.$PUUID.drush.in:2222/~/repository.git

	# And push all code
	git push pantheon HEAD:master --force

	# Change connection mode back to SFTP so we can install
	terminus site set-connection-mode --site="$PUUID" --env="$PENV" --mode=sftp

	# Install the site
	terminus drush --site="$PUUID" --env="$PENV" "site-install --account-pass='$SITEPASS' --site-name='$SITE_NAME $NOW' -y"

	# Change connection mode back to Git
	terminus site set-connection-mode --site="$PUUID" --env="$PENV" --mode=git

	# Now, wake up the site
	terminus site wake --site="$PUUID" --env="$PENV"
fi
