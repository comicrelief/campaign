<?php

namespace Drupal\yamlform\Element;

use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Render\Element;

/**
 * Provides a form element for entering a signature.
 *
 * @FormElement("yamlform_signature")
 */
class YamlFormSignature extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#process' => [
        [$class, 'processAjaxForm'],
        [$class, 'processGroup'],
      ],
      '#pre_render' => [
        [$class, 'preRenderYamlFormSignature'],
      ],
      '#theme' => 'input__yamlform_signature',
      '#theme_wrappers' => ['form_element'],
    ];
  }

  /**
   * Prepares a #type 'yamlform_signature' render element for input.html.twig.
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #description, #min, #max, #attributes,
   *   #step.
   *
   * @return array
   *   The $element with prepared variables ready for input.html.twig.
   */
  public static function preRenderYamlFormSignature($element) {
    $element['#attributes']['type'] = 'hidden';
    Element::setAttributes($element, ['name', 'value']);
    static::setAttributes($element, ['js-yamlform-signature', 'form-yamlform-signature']);

    $build = [
      '#prefix' => '<div class="js-yamlform-signature-pad yamlform-signature-pad">',
      '#suffix' => '</div>',
    ];
    $build['reset'] = [
      '#type' => 'button',
      '#value' => t('Reset'),
    ];
    $build['canvas'] = [
      '#type' => 'html_tag',
      '#tag' => 'canvas',
    ];
    $element['#children'] = $build;

    $element['#attached']['library'][] = 'yamlform/yamlform.element.signature';
    return $element;
  }

}
