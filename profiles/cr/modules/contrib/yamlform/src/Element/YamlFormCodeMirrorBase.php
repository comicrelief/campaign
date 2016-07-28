<?php

/**
 * @file
 * Contains \Drupal\yamlform\Element\YamlFormCodeMirrorBase.
 */

namespace Drupal\yamlform\Element;

use Drupal\Core\Render\Element\Textarea;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a base form element for YAML form CodeMirror elements.
 */
abstract class YamlFormCodeMirrorBase extends Textarea {

  /**
   * Type of code.
   *
   * @var string
   */
  static protected $type;

  /**
   * An associative array of supported CodeMirror modes by type.
   *
   * @var array
   */
  static protected $modes = [
    'html' => 'text/html',
    'text' => 'text/plain',
    'yaml' => 'text/x-yaml',
  ];

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#cols' => 60,
      '#rows' => 5,
      '#resizable' => 'vertical',
      '#process' => [
        [$class, 'processYamlFormCodeMirror'],
        [$class, 'processAjaxForm'],
        [$class, 'processGroup'],
      ],
      '#pre_render' => [
        [$class, 'preRenderYamlFormCodeMirror'],
        [$class, 'preRenderGroup'],
      ],
      '#element_validate' => [
        [$class, 'validateYamlFormCodeMirror'],
      ],
      '#theme' => 'textarea',
      '#theme_wrappers' => ['form_element'],
    ];
  }

  /**
   * Processes a 'yamlform_codemirror_yaml' element.
   */
  public static function processYamlFormCodeMirror(&$element, FormStateInterface $form_state, &$complete_form) {
    $element['#attached']['library'][] = 'yamlform/codemirror.' . static::$type;
    return $element;
  }

  /**
   * Form element validation handler for #type 'yamlform_codemirror_yaml'.
   */
  public static function validateYamlFormCodeMirror(&$element, FormStateInterface $form_state, &$complete_form) {
    if ($errors = static::getErrors($element, $form_state, $complete_form)) {
      $build = [
        'title' => [
          '#markup' => t('%title is not valid.', ['%title' => $element['#title']]),
        ],
        'errors' => [
          '#theme' => 'item_list',
          '#items' => $errors,
        ],
      ];
      $form_state->setError($element, \Drupal::service('renderer')->render($build));
    }
  }

  /**
   * Prepares a #type 'yamlform_codemirror_yaml' render element for theme_input().
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #description, #size, #maxlength,
   *   #placeholder, #required, #attributes.
   *
   * @return array
   *   The $element with prepared variables ready for theme_input().
   */
  public static function preRenderYamlFormCodeMirror($element) {
    static::setAttributes($element, ['js-yamlform-codemirror', 'yamlform-codemirror', static::$type]);
    $element['#attributes']['data-yamlform-codemirror-mode'] = static::getMode(static::$type);
    return $element;
  }

  /**
   * Get the CodeMirror mode for specified type.
   *
   * @param string $type
   *   Type of code.
   *
   * @return string
   *   The CodeMirror mode.
   */
  public static function getMode($type) {
    return (isset(static::$modes[$type])) ? static::$modes[$type] : static::$modes['text'];
  }

  /**
   * Get validation errors.
   */
  protected static function getErrors(&$element, FormStateInterface $form_state, &$complete_form) {
    return NULL;
  }

}
