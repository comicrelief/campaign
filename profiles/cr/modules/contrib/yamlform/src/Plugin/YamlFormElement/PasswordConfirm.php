<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\PasswordConfirm.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a 'password_confirm' element.
 *
 * @YamlFormElement(
 *   id = "password_confirm",
 *   label = @Translation("Password confirm")
 * )
 */
class PasswordConfirm extends Password {

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    $element['#element_validate'][] = [get_class($this), 'validate'];
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultValue(array &$element, $default_value) {
    if (is_string($element['#default_value'])) {
      $element['#default_value'] = [
        'pass1' => $element['#default_value'],
        'pass2' => $element['#default_value'],
      ];
    }
  }

  /**
   * Form API callback. Convert password confirm array to single value.
   */
  public static function validate(array &$element, FormStateInterface $form_state) {
    $name = $element['#name'];
    $value = $form_state->getValue($name);
    $form_state->setValue($name, $value['pass1']);
  }

}
