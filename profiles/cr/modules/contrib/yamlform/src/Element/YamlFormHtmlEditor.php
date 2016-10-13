<?php

namespace Drupal\yamlform\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Textarea;

/**
 * Provides a form element for entering HTML using CKEditor or CodeMirror.
 *
 * @FormElement("yamlform_html_editor")
 */
class YamlFormHtmlEditor extends Textarea {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    $info = parent::getInfo();
    $info['#pre_render'][] = [$class, 'preRenderYamlFormHtmlEditor'];
    $info['#element_validate'][] = [$class, 'validateYamlFormHtmlEditor'];
    return $info;
  }

  /**
   * Prepares a #type 'html_editor' render element for input.html.twig.
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #return_value, #description, #required,
   *   #attributes, #checked.
   *
   * @return array
   *   The $element with prepared variables ready for input.html.twig.
   */
  public static function preRenderYamlFormHtmlEditor($element) {
    if (\Drupal::config('yamlform.settings')->get('ui.html_editor_disabled')) {
      $element['#mode'] = 'html';
      $element = YamlFormCodeMirror::preRenderYamlFormCodeMirror($element);
    }
    else {
      $element['#attached']['library'][] = 'yamlform/yamlform.element.html_editor';
    }
    return $element;
  }

  /**
   * Form element validation handler for #type 'yamlform_html_editor'.
   */
  public static function validateYamlFormHtmlEditor(&$element, FormStateInterface $form_state, &$complete_form) {
    $value = $element['#value'];
    $form_state->setValueForElement($element, trim($value));
  }

}
