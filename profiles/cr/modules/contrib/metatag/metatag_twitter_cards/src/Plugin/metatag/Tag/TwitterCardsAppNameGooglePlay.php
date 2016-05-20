<?php
/**
 * @file
 * Contains \Drupal\metatag_twitter_cards\Plugin\metatag\Tag\TwitterCardsAppNameGooglePlay.
 */

namespace Drupal\metatag_twitter_cards\Plugin\metatag\Tag;

use Drupal\metatag\Plugin\metatag\Tag\MetaPropertyBase;

/**
 * The Twitter Cards app name Google Play metatag.
 *
 * @MetatagTag(
 *   id = "twitter_cards_app_name_google_play",
 *   label = @Translation("Google Play app name"),
 *   description = @Translation("The name of the app in the Google Play app store."),
 *   name = "twitter:app:name:googleplay",
 *   group = "twitter_cards",
 *   weight = 306,
 *   type = "string",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class TwitterCardsAppNameGooglePlay extends MetaPropertyBase {
}
