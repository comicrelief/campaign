<?php
/**
 * @file
 * Contains \Drupal\metatag_twitter_cards\Plugin\metatag\Tag\TwitterCardsGalleryImage1.
 */

namespace Drupal\metatag_twitter_cards\Plugin\metatag\Tag;

use Drupal\metatag\Plugin\metatag\Tag\MetaPropertyBase;

/**
 * The Twitter Cards gallery image1 metatag.
 *
 * @MetatagTag(
 *   id = "twitter_cards_gallery_image1",
 *   label = @Translation("2nd gallery image"),
 *   description = @Translation("A URL to the image representing the second photo in your gallery. This will be able to extract the URL from an image field."),
 *   name = "twitter:gallery:image1",
 *   group = "twitter_cards",
 *   weight = 201,
 *   type = "image",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class TwitterCardsGalleryImage1 extends MetaPropertyBase {
}
