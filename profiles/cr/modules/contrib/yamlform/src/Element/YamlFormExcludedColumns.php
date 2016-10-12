<?php

namespace Drupal\yamlform\Element;

/**
 * Provides a form element for form excluded columns (submission field and elements).
 *
 * @FormElement("yamlform_excluded_columns")
 */
class YamlFormExcludedColumns extends YamlFormExcludedBase {

  /**
   * {@inheritdoc}
   */
  public static function getYamlFormExcludedHeader() {
    return [t('Title'), t('Name'), t('Date type/Element type')];
  }

  /**
   * {@inheritdoc}
   */
  public static function getYamlFormExcludedOptions(array $element) {
    $options = [];

    /** @var \Drupal\yamlform\YamlFormSubmissionStorageInterface $submission_storage */
    $submission_storage = \Drupal::entityTypeManager()->getStorage('yamlform_submission');
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
