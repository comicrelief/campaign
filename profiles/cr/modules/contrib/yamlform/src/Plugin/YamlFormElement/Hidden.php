<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

/**
 * Provides a 'hidden' element.
 *
 * @YamlFormElement(
 *   id = "hidden",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Hidden.php/class/Hidden",
 *   label = @Translation("Hidden"),
 *   category = @Translation("Basic elements"),
 * )
 */
class Hidden extends TextBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return [
      'value' => '',
    ];
  }

}
