<?php
/**
 * @file
 * Contains \Drupal\metatag_twitter_cards\Plugin\metatag\Tag\TwitterCardsLabel2.
 */

namespace Drupal\metatag_twitter_cards\Plugin\metatag\Tag;

use \Drupal\metatag\Plugin\metatag\Tag\MetaPropertyBase;

/**
 * Provides a plugin for the 'twitter:label2' meta tag.
 *
 * @MetatagTag(
 *   id = "twitter_cards_label2",
 *   label = @Translation("Label 2"),
 *   description = @Translation("This field expects a string, and you can specify values for labels such as price, items in stock, sizes, etc."),
 *   name = "twitter:label2",
 *   group = "twitter_cards",
 *   weight = 502,
 *   type = "string",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class TwitterCardsLabel2 extends MetaPropertyBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}
