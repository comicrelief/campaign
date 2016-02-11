## Behat tests

To set up Behat tests to run locally, run

	composer install

Then, to run tests locally, modify the values below to reflect your site setup
(@todo integrate this with our Phing setup).

```
export BASE_URL='http://[[[FILL THIS OUT]]]';
export DRUPAL_ROOT='[[[FILL THIS OUT]]]';
export DRUSH_ALIAS='[[[FILL THIS OUT]]];
export BEHAT_PARAMS='{"extensions":{"Behat\\MinkExtension":{"base_url":"'$BASE_URL'"},"Drupal\\DrupalExtension":{"drupal":{"drupal_root":"'$DRUPAL_ROOT'"},"drush":{"alias":"'$DRUSH_ALIAS'"}}}}';
```

Finally, run the Behat command

	./vendor/bin/behat