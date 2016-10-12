<?php

namespace Drupal\yamlform\Utility;

/**
 * Helper class form based methods.
 */
class YamlFormHelper {

  /**
   * Cleanup form state values.
   *
   * @param array $values
   *   An array of form state values.
   * @param array $keys
   *   (optional) An array of custom keys to be removed.
   *
   * @return array
   *   The values without default keys like
   *   'form_build_id', 'form_token', 'form_id', 'op', 'actions', etc...
   */
  public static function cleanupFormStateValues(array $values, array $keys = []) {
    // Remove default FAPI values.
    unset(
      $values['form_build_id'],
      $values['form_token'],
      $values['form_id'],
      $values['op']
    );

    // Remove any objects.
    foreach ($values as $key => $value) {
      if (is_object($value)) {
        unset($values[$key]);
      }
    }

    // Remove custom keys.
    foreach ($keys as $key) {
      unset($values[$key]);
    }
    return $values;
  }

}
