<?php

/**
 * @file
 * Contains \Drupal\yamlform\Element\YamlFormCodeMirrorHtml.
 */

namespace Drupal\yamlform\Element;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form element for input of HTML using CodeMirror.
 *
 * @FormElement("yamlform_codemirror_html")
 */
class YamlFormCodeMirrorHtml extends YamlFormCodeMirrorBase {

  /**
   * {@inheritdoc}
   */
  static protected $type = 'html';

  /**
   * {@inheritdoc}
   */
  public static function getErrors(&$element, FormStateInterface $form_state, &$complete_form) {
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
  }

}
