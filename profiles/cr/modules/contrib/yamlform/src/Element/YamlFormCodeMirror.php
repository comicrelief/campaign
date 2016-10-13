<?php

namespace Drupal\yamlform\Element;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Render\Element\Textarea;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form element for HTML, YAML, or Plain text using CodeMirror.
 *
 * @FormElement("yamlform_codemirror")
 */
class YamlFormCodeMirror extends Textarea {

  /**
   * An associative array of supported CodeMirror modes by type and mime-type.
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
      '#mode' => 'text',
      '#skip_validation' => FALSE,
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
   * Processes a 'yamlform_codemirror' element.
   */
  public static function processYamlFormCodeMirror(&$element, FormStateInterface $form_state, &$complete_form) {
    // Check that mode is defined and valid, if not default to (plain) text.
    if (empty($element['#mode']) || !isset(self::$modes[$element['#mode']])) {
      $element['#mode'] = 'text';
    }

    // Alter element based on its mode.
    switch ($element['#mode']) {
      case 'yaml':
        // Clear empty #default_value.
        if (isset($element['#default_value']) && $element['#default_value'] == '{  }') {
          $element['#default_value'] = '';
        }
        // Clear empty #value.
        if (isset($element['#value']) && $element['#value'] == '{  }') {
          $element['#value'] = '';
        }
        break;
    }

    return $element;
  }

  /**
   * Prepares a #type 'yamlform_code' render element for theme_element().
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #description, #size, #maxlength,
   *   #placeholder, #required, #attributes.
   *
   * @return array
   *   The $element with prepared variables ready for theme_element().
   */
  public static function preRenderYamlFormCodeMirror($element) {
    static::setAttributes($element, ['js-yamlform-codemirror', 'yamlform-codemirror', $element['#mode']]);
    $element['#attributes']['data-yamlform-codemirror-mode'] = static::getMode($element['#mode']);
    $element['#attached']['library'][] = 'yamlform/yamlform.element.codemirror.' . $element['#mode'];
    return $element;
  }

  /**
   * Form element validation handler for #type 'yamlform_codemirror'.
   */
  public static function validateYamlFormCodeMirror(&$element, FormStateInterface $form_state, &$complete_form) {
    if ($errors = static::getErrors($element, $form_state, $complete_form)) {
      $build = [
        'title' => [
          '#markup' => t('%title is not valid.', ['%title' => (isset($element['#title']) ? $element['#title'] : t('YAML'))]),
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
   * Get validation errors.
   */
  protected static function getErrors(&$element, FormStateInterface $form_state, &$complete_form) {
    if (!empty($element['#skip_validation'])) {
      return NULL;
    }

    switch ($element['#mode']) {
      case 'html':
        // @see: http://stackoverflow.com/questions/3167074/which-function-in-php-validate-if-the-string-is-valid-html
        // @see: http://stackoverflow.com/questions/5030392/x-html-validator-in-php
        libxml_use_internal_errors(TRUE);
        if (simplexml_load_string('<fragment>' . $element['#value'] . '</fragment>')) {
          return NULL;
        }

        $errors = libxml_get_errors();
        libxml_clear_errors();
        if (!$errors) {
          return NULL;
        }

        $messages = [];
        foreach ($errors as $error) {
          $messages[] = $error->message;
        }
        return $messages;

      case 'yaml';
        try {
          $value = trim($element['#value']);
          $data = Yaml::decode($value);
          if (!is_array($data) && $value) {
            throw new \Exception(t('YAML must contain an associative array of elements.'));
          }
          return NULL;
        }
        catch (\Exception $exception) {
          return [$exception->getMessage()];
        }

      default:
        return NULL;
    }
  }

  /**
   * Get the CodeMirror mode for specified type.
   *
   * @param string $mode
   *   Mode (text, html, or yaml).
   *
   * @return string
   *   The CodeMirror mode (aka mime type).
   */
  public static function getMode($mode) {
    return (isset(static::$modes[$mode])) ? static::$modes[$mode] : static::$modes['text'];
  }

}
