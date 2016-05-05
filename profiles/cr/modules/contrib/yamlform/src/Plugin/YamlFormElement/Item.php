<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\Item.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a 'item' element.
 *
 * @YamlFormElement(
 *   id = "item",
 *   label = @Translation("Item")
 * )
 */
class Item extends ContainerBase {

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    $element['#element_validate'][] = [get_class($this), 'validate'];
  }

  /**
   * Form API callback. Removes ignored element for $form_state values.
   */
  public static function validate(array &$element, FormStateInterface $form_state) {
    $name = $element['#name'];
    $form_state->unsetValue($name);
  }

}
