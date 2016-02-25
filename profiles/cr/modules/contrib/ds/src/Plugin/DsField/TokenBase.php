<?php

/**
 * @file
 * Contains \Drupal\ds\Plugin\DsField\TokenBase.
 */

namespace Drupal\ds\Plugin\DsField;

/**
 * The base plugin to create DS code fields.
 */
abstract class TokenBase extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $content = $this->content();
    $format = $this->format();
    $value = \Drupal::service('token')->replace($content, array($this->getEntityTypeId() => $this->entity()), array('clear' => TRUE));

    return array(
      '#type' => 'processed_text',
      '#text' => $value,
      '#format' => $format,
      '#filter_types_to_skip' => array(),
      '#langcode' => '',
    );
  }

  /**
   * Returns the format of the code field.
   */
  protected function format() {
    return 'plain_text';
  }

  /**
   * Returns the value of the code field.
   */
  protected function content() {
    return '';
  }

}
