<?php

namespace Drupal\yamlform\Element;

use Drupal\Core\Render\Element\Checkbox;

/**
 * Provides a form element for entering a toggle.
 *
 * @FormElement("yamlform_toggle")
 */
class YamlFormToggle extends Checkbox {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return [
      '#toggle_theme' => 'light',
      '#toggle_size' => 'medium',
      '#on_text' => '',
      '#off_text' => '',
    ] + parent::getInfo();
  }

  /**
   * Prepares a #type 'checkbox' render element for input.html.twig.
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #return_value, #description, #required,
   *   #attributes, #checked.
   *
   * @return array
   *   The $element with prepared variables ready for input.html.twig.
   */
  public static function preRenderCheckbox($element) {
    $element = parent::preRenderCheckbox($element);

    $element += [
      '#toggle_size' => 'medium',
      '#toggle_theme' => 'light',
      '#on_text' => '',
      '#off_text' => '',
    ];

    // Toggle heights.
    $sizes = ['large' => 36, 'medium' => 24, 'small' => 16];
    $height = $sizes[$element['#toggle_size']];
    if (!empty($element['#on_text']) || !empty($element['#off_text'])) {
      $width = $height * 3;
    }
    else {
      $width = $height * 2;
    }

    $attributes = [
      'class' => [
        'js-yamlform-toggle',
        'yamlform-toggle',
        'toggle',
        'toggle-' . $element['#toggle_size'],
        'toggle-' . $element['#toggle_theme'],
      ],
      'data-toggle-height' => $height,
      'data-toggle-width' => $width,
      'data-toggle-text-on' => $element['#on_text'],
      'data-toggle-text-off' => $element['#off_text'],
    ];

    $element['#children']['toggles'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => $attributes,
      '#attached' => [
        'library' => ['yamlform/yamlform.element.toggle'],
      ],
    ];

    return $element;
  }

}
