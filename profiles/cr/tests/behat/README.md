To run tests locally, modify the values below and then run them from command
line.

```
export BASE_URL='http://[[[FILL THIS OUT]]]';
export DRUPAL_ROOT='[[[FILL THIS OUT]]]';
export DRUSH_ALIAS='[[[FILL THIS OUT]]];
export BEHAT_PARAMS='{"extensions":{"Behat\\MinkExtension":{"base_url":"'$BASE_URL'"},"Drupal\\DrupalExtension":{"drupal":{"drupal_root":"'$DRUPAL_ROOT'"},"drush":{"alias":"'$DRUSH_ALIAS'"}}}}';
./vendor/bin/behat
```
