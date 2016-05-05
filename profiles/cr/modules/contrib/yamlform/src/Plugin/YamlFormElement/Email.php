<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\Email.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

/**
 * Provides a 'email' element.
 *
 * @YamlFormElement(
 *   id = "email",
 *   label = @Translation("Email")
 * )
 */
class Email extends TextFieldBase {

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
          '#url' => \Drupal::pathValidator()->getUrlIfValid('mailto:' . $value),
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
