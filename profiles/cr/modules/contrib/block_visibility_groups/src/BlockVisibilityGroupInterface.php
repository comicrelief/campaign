<?php

namespace Drupal\block_visibility_groups;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;

/**
 * Provides an interface for defining Block Visibility Group entities.
 */
interface BlockVisibilityGroupInterface extends ConfigEntityInterface, EntityWithPluginCollectionInterface {
  // Add get/set methods for your configuration properties here.
}
