<?php
/**
 * @file
 * Contains \Drupal\metatag_verification\Plugin\metatag\Tag\Yahoo.
 */

namespace Drupal\metatag_verification\Plugin\metatag\Tag;

use Drupal\metatag\Plugin\metatag\Tag\MetaNameBase;

/**
 * Provides a plugin for the 'y_key' meta tag.
 *
 * @MetatagTag(
 *   id = "yahoo",
 *   label = @Translation("Yahoo"),
 *   description = @Translation("A string provided by <a href=':yahoo'>Yahoo</a>.", arguments = { ":yahoo" = "http://www.yahoo.com/" }),
 *   name = "y_key",
 *   group = "site_verification",
 *   weight = 7,
 *   type = "label",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class Yahoo extends MetaNameBase {
}
