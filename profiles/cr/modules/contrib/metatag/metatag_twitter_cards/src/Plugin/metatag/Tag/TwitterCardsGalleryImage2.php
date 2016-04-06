<?php
/**
 * @file
 * Contains \Drupal\metatag_twitter_cards\Plugin\metatag\Tag\TwitterCardsGalleryImage2.
 */

namespace Drupal\metatag_twitter_cards\Plugin\metatag\Tag;

use Drupal\metatag\Plugin\metatag\Tag\MetaPropertyBase;

/**
 * The Twitter Cards gallery image2 metatag.
 *
 * @MetatagTag(
 *   id = "twitter_cards_gallery_image2",
 *   label = @Translation("3rd gallery image"),
 *   description = @Translation("A URL to the image representing the third photo in your gallery. This will be able to extract the URL from an image field."),
 *   name = "twitter:gallery:image2",
 *   group = "twitter_cards",
 *   weight = 202,
 *   type = "image",
 *   multiple = FALSE
 * )
 */
class TwitterCardsGalleryImage2 extends MetaPropertyBase {
}
