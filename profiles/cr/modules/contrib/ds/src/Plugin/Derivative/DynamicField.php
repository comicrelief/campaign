<?php

namespace Drupal\ds\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;

/**
 * Retrieves dynamic field plugin definitions.
 */
abstract class DynamicField extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {

    $custom_fields = \Drupal::configFactory()->listAll('ds.field.');

    foreach ($custom_fields as $config) {
      $field = \Drupal::config($config)->get();
      if ($field['type'] == $this->getType()) {
        foreach ($field['entities'] as $entity_type) {
          $key = $this->getKey($entity_type, $field);
          $this->derivatives[$key] = $base_plugin_definition;
          $this->derivatives[$key] += array(
            'title' => \Drupal::translation()->translate($field['label']),
            'properties' => $field['properties'],
            'entity_type' => $entity_type,
          );
          if (!empty($field['ui_limit'])) {
            $this->derivatives[$key]['ui_limit'] = explode("\n", $field['ui_limit']);
            // Ensure that all strings are trimmed, eg. don't have extra spaces,
            // \r chars etc.
            foreach ($this->derivatives[$key]['ui_limit'] as $k => $v) {
              $this->derivatives[$key]['ui_limit'][$k] = trim($v);
            }
          }
        }
      }
    }

    return $this->derivatives;
  }

  /**
   * {@inheritdoc}
   */
  protected function getType() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  protected function getKey($entity_type, $field) {
    return $entity_type . '-' . $field['id'];
  }

}
