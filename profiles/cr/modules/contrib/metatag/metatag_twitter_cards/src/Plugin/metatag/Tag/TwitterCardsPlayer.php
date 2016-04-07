<?php
/**
 * @file
 * Contains \Drupal\metatag_twitter_cards\Plugin\metatag\Tag\TwitterCardsPlayer.
 */

namespace Drupal\metatag_twitter_cards\Plugin\metatag\Tag;

use Drupal\metatag\Plugin\metatag\Tag\MetaPropertyBase;

/**
 * The Twitter Cards player metatag.
 *
 * @MetatagTag(
 *   id = "twitter_cards_player",
 *   label = @Translation("Media player URL"),
 *   description = @Translation("The full URL for loading a media player. Required when using a Media player card."),
 *   name = "twitter:player",
 *   group = "twitter_cards",
 *   weight = 400,
 *   type = "uri",
 *   multiple = FALSE
 * )
 */
class TwitterCardsPlayer extends MetaPropertyBase {
}
