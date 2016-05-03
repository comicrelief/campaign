<?php

/**
 * @file
 * Contains \Drupal\yamlform\YamlFormOptionsInterface.
 */

namespace Drupal\yamlform;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a YAML form options entity.
 */
interface YamlFormOptionsInterface extends ConfigEntityInterface {

  /**
   * Get options (YAML) as an associative array.
   *
   * @return array|bool
   *   Inputs as an associative array. Returns FALSE is options YAML is invalid.
   */
  public function getOptions();

}
