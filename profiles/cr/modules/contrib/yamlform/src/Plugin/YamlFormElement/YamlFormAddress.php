<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormState;
use Drupal\yamlform\Element\YamlFormAddress as YamlFormAddressElement;

/**
 * Provides an 'address' element.
 *
 * @YamlFormElement(
 *   id = "yamlform_address",
 *   label = @Translation("Address"),
 *   category = @Translation("Composite elements"),
 *   multiline = TRUE,
 *   composite = TRUE,
 *   states_wrapper = TRUE,
 * )
 */
class YamlFormAddress extends YamlFormCompositeBase {

  /**
   * {@inheritdoc}
   */
  protected function getCompositeElements() {
    return YamlFormAddressElement::getCompositeElements();
  }

  /**
   * {@inheritdoc}
   */
  protected function getInitializedCompositeElement(array &$element) {
    $form_state = new FormState();
    $form_completed = [];
    return YamlFormAddressElement::processYamlFormComposite($element, $form_state, $form_completed);
  }

  /**
   * {@inheritdoc}
   */
  protected function formatLines(array $element, array $value) {
    $lines = [];
    if (!empty($value['address'])) {
      $lines['address'] = $value['address'];
    }
    if (!empty($value['address_2'])) {
      $lines['address_2'] = $value['address_2'];
    }
    $location = '';
    if (!empty($value['city'])) {
      $location .= $value['city'];
    }
    if (!empty($value['state_province'])) {
      $location .= ($location) ? ', ' : '';
      $location .= $value['state_province'];
    }
    if (!empty($value['postal_code'])) {
      $location .= ($location) ? '. ' : '';
      $location .= $value['postal_code'];
    }
    if ($location) {
      $lines['location'] = $location;
    }
    if (!empty($value['country'])) {
      $lines['country'] = $value['country'];
    }
    return $lines;
  }

}
