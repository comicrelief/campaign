<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\TextField.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\yamlform\YamlFormElementBase;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a base 'textfield' class.
 */
abstract class TextFieldBase extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    // Convert custom #autocomplete property to a FAPI autocomplete route
    // that return YAML form options.
    if (isset($element['#autocomplete'])) {
      $element['#autocomplete_route_name'] = 'yamlform.options_autocomplete';
      $element['#autocomplete_route_parameters'] = [
        'yamlform' => $yamlform_submission->getYamlForm()->id(),
        'key' => $element['#key'],
      ];
    }

    // Input mask support.
    if (isset($element['#input_mask'])) {
      // See if the input mask is JSON by looking for 'name':, else assume it
      // is a mask pattern.
      $input_mask = $element['#input_mask'];
      if (preg_match("/^'[^']+'\s*:/", $input_mask)) {
        $element['#attributes']['data-inputmask'] = $input_mask;
      }
      else {
        $element['#attributes']['data-inputmask-mask'] = $input_mask;
      }

      $element['#attributes']['class'][] = 'js-yamlform-input-mask';
      $element['#attached']['library'][] = 'yamlform/jquery.inputmask';
    }
  }

}
