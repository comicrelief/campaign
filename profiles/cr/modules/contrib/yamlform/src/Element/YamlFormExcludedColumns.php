<?php

/**
 * @file
 * Contains \Drupal\yamlform\Element\YamlFormExcludedColumns.
 */

namespace Drupal\yamlform\Element;

/**
 * Provides a form element for YAML form excluded columns (submission field and inputs).
 *
 * @FormElement("yamlform_excluded_columns")
 */
class YamlFormExcludedColumns extends YamlFormExcludedBase {

  /**
   * {@inheritdoc}
   */
  public static function getYamlFormExcludedHeader() {
    return [t('Title'), t('Name'), t('Date type/Input type')];
  }

  /**
   * {@inheritdoc}
   */
  public static function getYamlFormExcludedOptions(array &$element) {
    $options = [];

    /** @var \Drupal\yamlform\YamlFormSubmissionStorageInterface $submission_storage */
    $submission_storage = \Drupal::entityManager()->getStorage('yamlform_submission');
    $field_definitions = $submission_storage->getFieldDefinitions();

    foreach ($field_definitions as $key => $field_definition) {
      $options[$key] = [
        ['title' => $field_definition['title']],
        ['name' => $key],
        ['type' => $field_definition['type']],
      ];
    }
    $options += parent::getYamlFormExcludedOptions($element);

    return $options;
  }

}
