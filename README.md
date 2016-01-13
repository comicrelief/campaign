# Comic Relief Campaign website

A work in progress. See http://confluence.comicrelief.com/display/RND17/Campaign+platform+notes

## How to set this up locally

Copy over example local settings and adapt the database URL

	cp sites/default/example.settings.local.php sites/default/settings.local.php

Then, [install Drush 8](http://x-team.com/2015/02/install-drush-8-drupal-8-without-throwing-away-drush-6-7/), and re-install the site.

	drush8 si cr --account-pass="admin" -y

## How to run this on Pantheon

Add Pantheon as a second remote

	git remote add pantheon ssh://codeserver.dev.f9291f1f-3819-4964-9c5b-c9f7d5500d28@codeserver.dev.f9291f1f-3819-4964-9c5b-c9f7d5500d28.drush.in:2222/~/repository.git

Now you can push to Pantheon to deploy this

https://dashboard.pantheon.io/sites/f9291f1f-3819-4964-9c5b-c9f7d5500d28#dev/code

How to deal with [settings.php on Pantheon](https://pantheon.io/docs/articles/drupal/configuring-settings-php/)



