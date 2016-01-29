# Comic Relief Campaign website

A work in progress. See http://confluence.comicrelief.com/display/RND17/Campaign+platform+notes

## How to set this up locally

### Install and configure Drush 8

Drush 8 is required for Drupal 8. Install instructions can be found [here](http://x-team.com/2015/02/install-drush-8-drupal-8-without-throwing-away-drush-6-7/).

### Install Phing

You first will need to install to install [Phing](www.phing.info), which is a PHP build tool that automates tasks such as re-installing the site, running migrate procedures, tests etc.

Download Phing from http://www.phing.info/trac/wiki/Users/Download and follow installation instructions. The preferred way is to install this using PEAR.

### Configure Phing 

Copy over `build.example.properties` to `build.properties`

	cp build.example.properties build.properties

And now adapt `build.properties` adding in your Drush 8 binary location, database connection details, and your local website URL.

### Using Phing

To install the site, now run

	phing install

To login to the site, for example run

	phing login

To remake all contrib modules (for example, when adding a new module), run

	phing make

To list all possible Phing targets, check

	phing -l

## How to run this on Pantheon

Add Pantheon as a second remote

	git remote add pantheon ssh://codeserver.dev.f9291f1f-3819-4964-9c5b-c9f7d5500d28@codeserver.dev.f9291f1f-3819-4964-9c5b-c9f7d5500d28.drush.in:2222/~/repository.git

Now you can push to Pantheon to deploy this

https://dashboard.pantheon.io/sites/f9291f1f-3819-4964-9c5b-c9f7d5500d28#dev/code

How to deal with [settings.php on Pantheon](https://pantheon.io/docs/articles/drupal/configuring-settings-php/)



