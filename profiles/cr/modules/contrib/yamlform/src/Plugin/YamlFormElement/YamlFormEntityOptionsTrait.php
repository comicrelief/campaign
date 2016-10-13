<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\yamlform\YamlFormSubmissionInterface;
use Drupal\yamlform\Element\YamlFormEntityTrait;

/**
 * Provides an 'entity_reference' with options trait.
 */
trait YamlFormEntityOptionsTrait {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    $properties = parent::getDefaultProperties() + [
      'target_type' => '',
      'selection_handler' => '',
      'selection_settings' => [],
    ];
    unset($properties['options']);
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    YamlFormEntityTrait::setOptions($element);
    parent::prepare($element, $yamlform_submission);
  }

  /**
   * {@inheritdoc}
   */
  protected function getElementSelectorInputsOptions(array $element) {
    YamlFormEntityTrait::setOptions($element);
    return parent::getElementSelectorInputsOptions($element);
  }

}
