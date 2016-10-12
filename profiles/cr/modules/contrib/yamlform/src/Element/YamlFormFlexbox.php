<?php

namespace Drupal\yamlform\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Container;

/**
 * Provides a render element for form flexbox.
 *
 * @FormElement("yamlform_flexbox")
 */
class YamlFormFlexbox extends Container {

  /**
   * {@inheritdoc}
   */
  public static function processContainer(&$element, FormStateInterface $form_state, &$complete_form) {
    $element = parent::processContainer($element, $form_state, $complete_form);
    $element['#attributes']['class'][] = 'yamlform-flexbox';
    $element['#attributes']['class'][] = 'js-yamlform-flexbox';
    if (isset($element['#align_items'])) {
      $element['#attributes']['class'][] = 'yamlform-flexbox--' . $element['#align_items'];
    }
    $element['#attached']['library'][] = 'yamlform/yamlform.element.flexbox';
    return $element;
  }

}
