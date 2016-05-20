<?php
/**
 * @file
 * Contains \Drupal\metatag_twitter_cards\Plugin\metatag\Tag\TwitterCardsData1.
 */

namespace Drupal\metatag_twitter_cards\Plugin\metatag\Tag;

use \Drupal\metatag\Plugin\metatag\Tag\MetaPropertyBase;

/**
 * Provides a plugin for the 'twitter:data1' meta tag.
 *
 * @MetatagTag(
 *   id = "twitter_cards_data1",
 *   label = @Translation("Data 1"),
 *   description = @Translation("This field expects a string, and allows you to specify the types of data you want to offer (price, country, etc.)."),
 *   name = "twitter:data1",
 *   group = "twitter_cards",
 *   weight = 501,
 *   type = "string",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class TwitterCardsData1 extends MetaPropertyBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}
