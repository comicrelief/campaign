<?php

/**
 * @file
 * Contains \Drupal\yamlform\Element\YamlFormCodeMirrorYaml.
 */

namespace Drupal\yamlform\Element;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form element for input of YAML text using CodeMirror.
 *
 * @FormElement("yamlform_codemirror_yaml")
 */
class YamlFormCodeMirrorYaml extends YamlFormCodeMirrorBase {

  /**
   * {@inheritdoc}
   */
  static protected $type = 'yaml';

  /**
   * {@inheritdoc}
   */
  public static function getErrors(&$element, FormStateInterface $form_state, &$complete_form) {
    try {
      $value = trim($element['#value']);
      $data = Yaml::decode($value);
      if (!is_array($data) && $value) {
        throw new \Exception(t('YAML must contain an associative array of inputs.'));
      }
      return NULL;
    }
    catch (\Exception $exception) {
      return [$exception->getMessage()];
    }
  }

}
