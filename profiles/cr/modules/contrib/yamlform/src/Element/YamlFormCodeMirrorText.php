<?php

/**
 * @file
 * Contains \Drupal\yamlform\Element\YamlFormCodeMirrorText.
 */

namespace Drupal\yamlform\Element;

/**
 * Provides a form element for input of Plain text using CodeMirror.
 *
 * @FormElement("yamlform_codemirror_text")
 */
class YamlFormCodeMirrorText extends YamlFormCodeMirrorBase {

  /**
   * {@inheritdoc}
   */
  static protected $type = 'text';

}
