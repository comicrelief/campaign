<?php
/**
 * @file
 * Contains \Drupal\metatag_twitter_cards\Plugin\metatag\Tag\TwitterCardsAppUrlIphone.
 */

namespace Drupal\metatag_twitter_cards\Plugin\metatag\Tag;

use Drupal\metatag\Plugin\metatag\Tag\MetaPropertyBase;

/**
 * The Twitter Cards app's custom URL scheme for iphone metatag.
 *
 * @MetatagTag(
 *   id = "twitter_cards_app_url_iphone",
 *   label = @Translation("iPhone app's custom URL scheme"),
 *   description = @Translation("The iPhone app's custom URL scheme (must include ""://"" after the scheme name)."),
 *   name = "twitter:app:url:iphone",
 *   group = "twitter_cards",
 *   weight = 302,
 *   type = "uri",
 *   multiple = FALSE
 * )
 */
class TwitterCardsAppUrlIphone extends MetaPropertyBase {
}
