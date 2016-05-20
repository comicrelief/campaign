<?php
/**
 * @file
 * Contains \Drupal\metatag_twitter_cards\Plugin\metatag\Tag\TwitterCardsAppIdGooglePlay.
 */

namespace Drupal\metatag_twitter_cards\Plugin\metatag\Tag;

use Drupal\metatag\Plugin\metatag\Tag\MetaPropertyBase;

/**
 * The Twitter Cards app id for Google Play metatag.
 *
 * @MetatagTag(
 *   id = "twitter_cards_app_id_google_play",
 *   label = @Translation("Google Play app ID"),
 *   description = @Translation("String value, and should be the numeric representation of your app's ID in Google Play."),
 *   name = "twitter:app:id:googleplay",
 *   group = "twitter_cards",
 *   weight = 307,
 *   type = "string",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class TwitterCardsAppIdGooglePlay extends MetaPropertyBase {
}
