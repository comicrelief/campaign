<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormState;
use Drupal\yamlform\Element\YamlFormCreditCard as YamlFormCreditCardElement;

/**
 * Provides a 'creditcard' element.
 *
 * @YamlFormElement(
 *   id = "yamlform_creditcard",
 *   label = @Translation("Credit card"),
 *   category = @Translation("Composite elements"),
 *   hidden = TRUE,
 *   multiline = TRUE,
 *   composite = TRUE,
 *   states_wrapper = TRUE,
 * )
 */
class YamlFormCreditCard extends YamlFormCompositeBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    $properties = parent::getDefaultProperties();
    unset(
      $properties['type__options'],
      $properties['expiration_month__options'],
      $properties['expiration_year__options']
    );
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  protected function getCompositeElements() {
    $elements = YamlFormCreditCardElement::getCompositeElements();
    unset($elements['expiration']);
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  protected function getInitializedCompositeElement(array &$element) {
    $form_state = new FormState();
    $form_completed = [];
    return YamlFormCreditCardElement::processYamlFormComposite($element, $form_state, $form_completed);
  }

}
