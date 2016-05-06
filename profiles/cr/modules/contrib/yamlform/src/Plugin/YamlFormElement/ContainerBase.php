<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\ContainerBase.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\yamlform\YamlFormElementBase;

/**
 * Provides a base 'container' class.
 */
abstract class ContainerBase extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  protected function build($format, array &$element, $value, array $options = []) {
    return [
      '#theme' => 'yamlform_container_base_' . $format,
      '#element' => $element,
      '#value' => $value,
      '#options' => $options,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultFormat() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormats() {
    return [];
  }

}
