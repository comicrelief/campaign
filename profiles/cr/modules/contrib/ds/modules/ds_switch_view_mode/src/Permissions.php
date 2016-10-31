<?php

namespace Drupal\ds_switch_view_mode;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides dynamic permissions of the ds switch view mode module.
 */
class Permissions {

  use StringTranslationTrait;

  /**
   * Returns an array of ds switch view mode permissions.
   */
  public function permissions() {
    $permissions = [];

    foreach (node_type_get_names() as $key => $name) {
      $permissions['ds switch ' . $key] = array(
        'title' => $this->t('Switch view modes on :type', array(':type' => $name)),
      );
    }

    return $permissions;
  }

}
