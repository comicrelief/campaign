<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\yamlform\YamlFormElementBase;
use Drupal\yamlform\YamlFormInterface;

/**
 * Provides a base 'container' class.
 */
abstract class ContainerBase extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return [
      'title' => '',
      'description' => '',

      'required' => FALSE,
      'title_display' => '',

      'attributes__class' => '',
      'attributes__style' => '',

      'admin_title' => '',
      'private' => FALSE,

      'flex' => 1,
      'states' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isInput(array $element) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isContainer(array $element) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  protected function build($format, array &$element, $value, array $options = []) {
    if (empty($value)) {
      return [];
    }

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

  /**
   * {@inheritdoc}
   */
  public function getTestValue(array $element, YamlFormInterface $yamlform) {
    // Containers should never have values and therefore should never have
    // a test value.
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getElementSelectorOptions(array $element) {
    return [];
  }

}
