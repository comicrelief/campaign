<?php

namespace Drupal\ds\Plugin\DsField;

/**
 * DS field markup base field.
 */
abstract class Markup extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $key = $this->key();
    if (isset($this->entity()->{$key}->value)) {
      $format = $this->format();

      return array(
        '#type' => 'processed_text',
        '#text' => $this->entity()->{$key}->value,
        '#format' => $format,
        '#filter_types_to_skip' => array(),
        '#langcode' => '',
      );
    }

    return array();
  }

  /**
   * Gets the key of the field that needs to be rendered.
   */
  protected function key() {
    return '';
  }

  /**
   * Gets the text format.
   */
  protected function format() {
    return 'filtered_html';
  }

}
