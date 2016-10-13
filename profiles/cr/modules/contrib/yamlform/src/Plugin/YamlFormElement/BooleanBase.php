<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\yamlform\YamlFormElementBase;

/**
 * Provides a base 'boolean' class.
 */
abstract class BooleanBase extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function formatText(array &$element, $value, array $options = []) {
    $format = $this->getFormat($element);

    switch ($format) {
      case 'value';
        return ($value) ? $this->t('Yes') : $this->t('No');

      default:
        return ($value) ? 1 : 0;
    }
  }

}
