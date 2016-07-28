<?php
/**
 * @file
 * Contains \Drupal\metatag_twitter_cards\Plugin\metatag\Tag\TwitterCardsLabel1.
 */

namespace Drupal\metatag_twitter_cards\Plugin\metatag\Tag;

use \Drupal\metatag\Plugin\metatag\Tag\MetaPropertyBase;

/**
 * Provides a plugin for the 'twitter:label1' meta tag.
 *
 * @MetatagTag(
 *   id = "twitter_cards_label1",
 *   label = @Translation("Label 1"),
 *   description = @Translation("This field expects a string, and you can specify values for labels such as price, items in stock, sizes, etc."),
 *   name = "twitter:label1",
 *   group = "twitter_cards",
 *   weight = 500,
 *   type = "string",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class TwitterCardsLabel1 extends MetaPropertyBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}
