<?php

namespace Drupal\context\Plugin;

use Drupal\Core\Plugin\DefaultLazyPluginCollection;

class ContextReactionPluginCollection extends DefaultLazyPluginCollection {

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\context\ContextReactionInterface
   */
  public function &get($instance_id) {
    return parent::get($instance_id);
  }
}
