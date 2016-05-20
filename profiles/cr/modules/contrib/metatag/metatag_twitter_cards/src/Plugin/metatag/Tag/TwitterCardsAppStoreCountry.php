<?php
/**
 * @file
 * Contains \Drupal\metatag_twitter_cards\Plugin\metatag\Tag\TwitterCardsAppStoreCountry.
 */

namespace Drupal\metatag_twitter_cards\Plugin\metatag\Tag;

use Drupal\metatag\Plugin\metatag\Tag\MetaPropertyBase;

/**
 * The Twitter Cards app store country code metatag.
 *
 * @MetatagTag(
 *   id = "twitter_cards_app_store_country",
 *   label = @Translation("App store country"),
 *   description = @Translation("If your application is not available in the US App Store, you must set this value to the two-letter country code for the App Store that contains your application."),
 *   name = "twitter:app:country",
 *   group = "twitter_cards",
 *   weight = 300,
 *   type = "string",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class TwitterCardsAppStoreCountry extends MetaPropertyBase {
}
