<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\Telephone.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

/**
 * Provides a 'tel' element.
 *
 * @YamlFormElement(
 *   id = "tel",
 *   label = @Translation("Telephone")
 * )
 */
class Telephone extends TextFieldBase {

  /**
   * {@inheritdoc}
   */
  public function formatHtml(array &$element, $value, array $options = []) {
    $format = $this->getFormat($element);
    switch ($format) {
      case 'link':
        return [
          '#type' => 'link',
          '#title' => $value,
          '#url' => \Drupal::pathValidator()->getUrlIfValid('tel:' . $value),
        ];

      default:
        return parent::formatHtml($element, $value, $options);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultFormat() {
    return 'link';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormats() {
    return parent::getFormats() + [
      'link' => $this->t('Link'),
    ];
  }

}
