<?php

namespace Drupal\yamlform\Element;

use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form element for entering multiple comma delimited email addresses.
 *
 * @FormElement("yamlform_email_multiple")
 */
class YamlFormEmailMultiple extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#description' => $this->t('Multiple email addresses may be separated by commas.'),
      '#size' => 60,
      '#allow_tokens' => FALSE,
      '#process' => [
        [$class, 'processAutocomplete'],
        [$class, 'processAjaxForm'],
        [$class, 'processPattern'],
      ],
      '#element_validate' => [
        [$class, 'validateYamlFormEmailMultiple'],
      ],
      '#pre_render' => [
        [$class, 'preRenderYamlFormEmailMultiple'],
      ],
      '#theme' => 'input__email_multiple',
      '#theme_wrappers' => ['form_element'],
    ];
  }

  /**
   * Form element validation handler for #type 'email_multiple'.
   */
  public static function validateYamlFormEmailMultiple(&$element, FormStateInterface $form_state, &$complete_form) {
    $value = trim($element['#value']);
    $form_state->setValueForElement($element, $value);

    if ($value) {
      $values = preg_split('/\s*,\s*/', $value);
      foreach ($values as $value) {
        // Allow tokens to be be include in multiple email list.
        if (!empty($element['#allow_tokens'] && preg_match('/^\[.*\]$/', $value))) {
          continue;
        }

        if (!\Drupal::service('email.validator')->isValid($value)) {
          $form_state->setError($element, t('The email address %mail is not valid.', ['%mail' => $value]));
          return;
        }
      }
    }
  }

  /**
   * Prepares a #type 'email_multiple' render element for theme_element().
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #description, #size, #maxlength,
   *   #placeholder, #required, #attributes.
   *
   * @return array
   *   The $element with prepared variables ready for theme_element().
   */
  public static function preRenderYamlFormEmailMultiple($element) {
    $element['#attributes']['type'] = 'text';
    Element::setAttributes($element, ['id', 'name', 'value', 'size', 'maxlength', 'placeholder']);
    static::setAttributes($element, ['form-textfield', 'form-email-multiple']);
    return $element;
  }

}
