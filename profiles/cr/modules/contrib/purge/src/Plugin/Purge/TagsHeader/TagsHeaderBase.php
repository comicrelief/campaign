<?php

/**
 * @file
 * Contains \Drupal\purge\Plugin\Purge\TagsHeader\TagsHeaderBase.
 */

namespace Drupal\purge\Plugin\Purge\TagsHeader;

use Drupal\Core\Plugin\PluginBase;
use Drupal\purge\Plugin\Purge\TagsHeader\TagsHeaderInterface;

/**
 * Base implementation for plugins that add and format a cache tags header.
 */
abstract class TagsHeaderBase extends PluginBase implements TagsHeaderInterface {

  /**
   * {@inheritdoc}
   */
  public function getHeaderName() {
    return $this->getPluginDefinition()['header_name'];
  }

  /**
   * {@inheritdoc}
   */
  public function getValue(array $tags) {
    return implode(' ', $tags);
  }

}
