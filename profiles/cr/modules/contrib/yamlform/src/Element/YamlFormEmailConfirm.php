<?php

namespace Drupal\yamlform\Element;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\CompositeFormElementTrait;

/**
 * Provides a form element requiring users to double-element and confirm an email address.
 *
 * Formats as a pair of email addresses fields, which do not validate unless
 * the two entered email addresses match.
 *
 * @FormElement("yamlform_email_confirm")
 */
class YamlFormEmailConfirm extends FormElement {

  use CompositeFormElementTrait;

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#size' => 60,
      '#process' => [
        [$class, 'processEmailConfirm'],
      ],
      '#pre_render' => [
        [$class, 'preRenderCompositeFormElement'],
      ],
      '#theme_wrappers' => ['container'],
      '#required' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input === FALSE) {
      if (!isset($element['#default_value'])) {
        $element['#default_value'] = '';
      }
      $element['mail_2'] = $element['mail_1'] = $element['#default_value'];
      return $element;
    }
    return NULL;
  }

  /**
   * Expand an email confirm field into two HTML5 email elements.
   */
  public static function processEmailConfirm(&$element, FormStateInterface $form_state, &$complete_form) {
    $element['#tree'] = TRUE;

    // Get shared properties.
    $shared_properties = [
      '#title_display',
      '#description_display',
      '#size',
      '#maxlength',
      '#pattern',
      '#required',
      '#placeholder',
      '#attributes',
    ];
    $element_shared_properties = ['#type' => 'email'] + array_intersect_key($element, array_combine($shared_properties, $shared_properties));

    // Get mail 1 email element.
    $mail_1_properties = [
      '#title',
      '#description',
    ];
    $element['mail_1'] = $element_shared_properties + array_intersect_key($element, array_combine($mail_1_properties, $mail_1_properties));
    $element['mail_1']['#attributes']['class'][] = 'yamlform-email';
    $element['mail_1']['#value'] = empty($element['#value']) ? NULL : $element['#value']['mail_1'];

    // Build mail_2 confirm email element.
    $element['mail_2'] = $element_shared_properties;
    $element['mail_2']['#title'] = t('Confirm email');
    foreach ($element as $key => $value) {
      if (strpos($key, '#confirm__') === 0) {
        $element['mail_2'][str_replace('#confirm__', '#', $key)] = $value;
      }
    }
    $element['mail_2']['#attributes']['class'][] = 'yamlform-email-confirm';
    $element['mail_2']['#value'] = empty($element['#value']) ? NULL : $element['#value']['mail_2'];

    // Remove properties that are being applied to the sub elements.
    $element['#required'] = FALSE;
    unset($element['#title']);
    unset($element['#description']);
    unset($element['#maxlength']);
    unset($element['#atributes']);

    $element['#element_validate'] = [[get_called_class(), 'validateEmailConfirm']];

    return $element;
  }

  /**
   * Validates an email confirm element.
   */
  public static function validateEmailConfirm(&$element, FormStateInterface $form_state, &$complete_form) {

    $mail_1 = trim($element['mail_1']['#value']);
    $mail_2 = trim($element['mail_2']['#value']);
    $has_access = (!isset($element['#access']) || $element['#access'] === TRUE);
    if ($has_access) {
      if ((!empty($mail_1) || !empty($mail_2)) && strcmp($mail_1, $mail_2)) {
        $form_state->setError($element['mail_2'], t('The specified email addresses do not match.'));
      }
      else {
        // NOTE: Only mail_1 needs to be validated since mail_2 is the same value.
        // Verify the required value.
        if ($element['mail_1']['#required'] && empty($mail_1)) {
          if (isset($element['#required_error'])) {
            $form_state->setError($element, $element['#required_error']);
          }
          elseif (isset($element['mail_1']['#title'])) {
            $form_state->setError($element, t('@name field is required.', ['@name' => $element['mail_1']['#title']]));
          }
          else {
            $form_state->setError($element);
          }
        }
        // Verify that the value is not longer than #maxlength.
        if (isset($element['mail_1']['#maxlength']) && Unicode::strlen($mail_1) > $element['mail_1']['#maxlength']) {
          $t_args = [
            '@name' => $element['mail_1']['#title'],
            '%max' => $element['mail_1']['#maxlength'],
            '%length' => Unicode::strlen($mail_1),
          ];
          $form_state->setError($element, t('@name cannot be longer than %max characters but is currently %length characters long.', $t_args));
        }
      }
    }

    // Email field must be converted from a two-element array into a single
    // string regardless of validation results.
    $form_state->setValueForElement($element['mail_1'], NULL);
    $form_state->setValueForElement($element['mail_2'], NULL);
    $form_state->setValueForElement($element, $mail_1);
  }

}
