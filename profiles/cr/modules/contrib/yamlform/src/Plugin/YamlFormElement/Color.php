<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\Color.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\yamlform\YamlFormElementBase;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a 'color' element.
 *
 * @YamlFormElement(
 *   id = "color",
 *   label = @Translation("Color")
 * )
 */
class Color extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    $element['#attached']['library'][] = 'yamlform/yamlform.element.color';
  }

  /**
   * {@inheritdoc}
   */
  public function formatHtml(array &$element, $value, array $options = []) {
    if (empty($value)) {
      return '';
    }

    $format = $this->getFormat($element);
    switch ($format) {
      case 'swatch':
        return [
          '#theme' => 'yamlform_element_color_value_swatch',
          '#element' => $element,
          '#value' => $value,
          '#options' => $options,
        ];

      default:
        return parent::formatHtml($element, $value, $options);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultFormat() {
    return 'swatch';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormats() {
    return parent::getFormats() + [
      'swatch' => $this->t('Color swatch'),
    ];
  }

}
