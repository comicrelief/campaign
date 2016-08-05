<?php

/**
 * @file
 * Contains \Drupal\url_embed\Plugin\EmbedType\Url.
 */

namespace Drupal\url_embed\Plugin\EmbedType;

use Drupal\embed\EmbedType\EmbedTypeBase;

/**
 * URL embed type.
 *
 * @EmbedType(
 *   id = "url",
 *   label = @Translation("URL")
 * )
 */
class Url extends EmbedTypeBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultIconUrl() {
    return file_create_url(drupal_get_path('module', 'url_embed') . '/js/plugins/drupalurl/urlembed.png');
  }
}
