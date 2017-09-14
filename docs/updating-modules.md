## Updating modules

All Drupal modules, themes, core and libraries are defined in [composer.json](composer.json).

### How to update a module?

Whenever you want to update a module to a newer version, you need to follow a series of steps.

First of all, make sure you are using a *clean database*, so run `composer drupal:install`

Then, require the new version of the module with

	composer require metatag:1.1

Make sure to also read the release notes of the new module, so you can be sure to be prepared for any drastic updates.

#### Test upgrade path

You can commit this now to the feature branch you are working on. Next, you need to rebuild the codebase to use the newer version

	composer update

You now need to test the *upgrade path* from the old to the new version, so run the update process via drush, like

	drush updatedb

	The following updates are pending:
	metatag module :
		8002 -   ...

If all is fine, you need to test that the upgrade path worked, first by running our automated test suite, and then also make a series of manual tests, that specifically involve the module thatyou just updated. In our case, we might want to test the metatags on the different pages.

Finally, review your preview environment on platform.sh and review automatic tests on Travis CI in your Pull Request.

#### Test upgrade path in sites like RND17

For the more complex upgrades (e.g. an important upgrade to paragraphs), it is advised to also test this in sites that implement the `campaign` profile, such as RND17.

To test this, you need to [follow RND17 instructions](https://github.com/comicrelief/rnd17#updating-the-base-profile) on how to implement an upgrade of the profile.

Don't forget to change the base branch of [composer.json](https://github.com/comicrelief/rnd17/blob/develop/composer.json) to use your feature branch, like

	composer require comicrelief/campaign:dev-feature_XXX_MY_MODULE_UPDATE
