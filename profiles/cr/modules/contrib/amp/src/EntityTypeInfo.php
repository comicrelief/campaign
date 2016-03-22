<?php

/**
 * @file
 * Contains Drupal\amp\EntityTypeInfo.
 */

namespace Drupal\amp;

/**
 * Service class for retrieving and manipulating entity type information.
 */
class EntityTypeInfo {

  /**
   * Returns a list of AMP-enabled content types.
   *
   * @return array
   *   An array of bundles that have AMP view modes enabled.
   */
  public function getAmpEnabledTypes() {
    $enabled_types = [];
    if ($cache = \Drupal::cache()->get('amp_enabled_types')) {
      $enabled_types = $cache->data;
    }
    else {
      $node_types = array_keys(node_type_get_names());
      foreach ($node_types as $node_type) {
        $amp_display = \Drupal::entityManager()
          ->getStorage('entity_view_display')
          ->load('node.' . $node_type . '.amp');
        if ($amp_display && $amp_display->status()) {
          $enabled_types[] = $node_type;
        }
      }
      \Drupal::cache()->set('amp_enabled_types', $enabled_types);
    }
    return array_combine($enabled_types, $enabled_types);
  }

  /**
   * Returns a formatted list of AMP-enabled content types.
   *
   * @return array
   *   A list of content types that provides the following:
   *     - Each content type enabled on the site.
   *     - The enabled/disabled status for each content type.
   *     - A link to enable/disable view modes for each content type.
   *     - A link to configure the AMP view mode, if enabled.
   */
  public function getFormattedAmpEnabledTypes() {
    $enabled_types = !empty($this->getAmpEnabledTypes()) ? $this->getAmpEnabledTypes() : array();
    $node_types = node_type_get_names();
    $node_status_list = array();
    foreach ($node_types as $bundle => $label) {
      $configure = t('/admin/structure/types/manage/:bundle/display/amp?destination=/admin/config/content/amp', array(':bundle' => $bundle));
      $enable_disable = t('/admin/structure/types/manage/:bundle/display?destination=/admin/config/content/amp', array(':bundle' => $bundle));
      if (in_array($bundle, $enabled_types)) {
        $node_status_list[] = t(':label is <em>enabled</em>: <a href=":configure">Configure AMP view mode</a> or <a href=":enable_disable">Disable AMP display</a>', array(
            ':label' => $label,
            ':configure' => $configure,
            ':enable_disable' => $enable_disable,
          ));
      }
      else {
        $node_status_list[] = t(':label is <em>disabled</em>: <a href=":enable_disable">Enable AMP in Custom Display Settings</a>', array(
            ':label' => $label,
            ':enable_disable' => $enable_disable,
          ));
      }
    }
    return $node_status_list;
  }
}
