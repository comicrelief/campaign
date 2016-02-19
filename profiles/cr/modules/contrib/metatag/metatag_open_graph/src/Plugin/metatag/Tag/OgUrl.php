<?php
/**
 * @file
 * Contains \Drupal\metatag_open_graph\Plugin\metatag\Tag\OgUrl.
 */

namespace Drupal\metatag_open_graph\Plugin\metatag\Tag;

use \Drupal\metatag\Plugin\metatag\Tag\MetaPropertyBase;

/**
 * The Open Graph "URL" meta tag.
 *
 * @MetatagTag(
 *   id = "og_url",
 *   label = @Translation("Page URL"),
 *   description = @Translation("Preferred page location or URL to help eliminate duplicate content for search engines, e.g., <em>http://www.imdb.com/title/tt0117500/</em>."),
 *   name = "og:url",
 *   group = "open_graph",
 *   weight = 3,
 *   image = FALSE,
 *   multiple = FALSE
 * )
 */
class OgUrl extends MetaPropertyBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}
