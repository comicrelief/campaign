<?php

namespace Drupal\ds;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityInterface;
use Drupal\layout_plugin\Layout;

/**
 * Helper class that holds all the main Display Suite helper functions.
 */
class Ds {

  /**
   * Gets all Display Suite fields.
   *
   * @param string $entity_type
   *   The name of the entity.
   *
   * @return array
   *   Collection of fields.
   */
  public static function getFields($entity_type) {
    static $static_fields;

    if (!isset($static_fields[$entity_type])) {
      foreach (\Drupal::service('plugin.manager.ds')->getDefinitions() as $plugin_id => $plugin) {
        // Needed to get derivatives working.
        $plugin['plugin_id'] = $plugin_id;
        $static_fields[$plugin['entity_type']][$plugin_id] = $plugin;
      }
    }

    return isset($static_fields[$entity_type]) ? $static_fields[$entity_type] : array();
  }

  /**
   * Gets the value for a Display Suite field.
   *
   * @param string $key
   *   The key of the field.
   * @param array $field
   *   The configuration of a DS field.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The current entity.
   * @param string $view_mode
   *   The name of the view mode.
   * @param array $build
   *   The current built of the entity.
   *
   * @return \Drupal\ds\Plugin\DsField\DsFieldInterface
   *   Field instance.
   */
  public static function getFieldInstance($key, $field, EntityInterface $entity, $view_mode, $display, $build = array()) {
    $configuration = array(
      'field' => $field,
      'field_name' => $key,
      'entity' => $entity,
      'build' => $build,
      'view_mode' => $view_mode,
    );

    // Load the plugin.
    /* @var $field_instance \Drupal\ds\Plugin\DsField\DsFieldInterface */
    $field_instance = \Drupal::service('plugin.manager.ds')->createInstance($field['plugin_id'], $configuration);

    /* @var $display \Drupal\Core\Entity\Display\EntityDisplayInterface */
    if ($field_settings = $display->getThirdPartySetting('ds', 'fields')) {
      $settings = isset($field_settings[$key]['settings']) ? $field_settings[$key]['settings'] : array();
      // Unset field template settings.
      if (isset($settings['ft'])) {
        unset($settings['ft']);
      }

      $field_instance->setConfiguration($settings);
    }

    return $field_instance;
  }

  /**
   * Gets Display Suite layouts.
   */
  public static function getLayouts() {
    static $layouts = FALSE;

    if (!$layouts) {
      $layouts = Layout::layoutPluginManager()->getDefinitions();
    }

    return $layouts;
  }

  /**
   * Gets a display for a given entity.
   *
   * @param string $entity_type
   *   The name of the entity.
   * @param string $bundle
   *   The name of the bundle.
   * @param string $view_mode
   *   The name of the view mode.
   * @param bool $fallback
   *   Whether to fallback to default or not.
   *
   * @return array|bool $layout
   *   The display.
   */
  public static function getDisplay($entity_type, $bundle, $view_mode, $fallback = TRUE) {
    /* @var $entity_display \Drupal\Core\Entity\Display\EntityDisplayInterface */
    $entity_display = entity_load('entity_view_display', $entity_type . '.' . $bundle . '.' . $view_mode);
    if ($entity_display) {
      $overridden = $entity_display->status();
    }
    else {
      $overridden = FALSE;
    }

    if ($entity_display) {
      return $entity_display;
    }

    // In case $view_mode is not found, check if we have a default layout,
    // but only if the view mode settings aren't overridden for this view mode.
    if ($view_mode != 'default' && !$overridden && $fallback) {
      /* @var $entity_default_display \Drupal\Core\Entity\Display\EntityDisplayInterface */
      $entity_default_display = entity_load('entity_view_display', $entity_type . '.' . $bundle . '.default');
      if ($entity_default_display) {
        return $entity_default_display;
      }
    }

    return FALSE;
  }

  /**
   * Checks if we can go on with Display Suite.
   *
   * In some edge cases, a view might be inserted into the view of an entity, in
   * which the same entity is available as well. This is simply not possible and
   * will lead to infinite loops, so you can temporarily disable DS completely
   * by setting this variable, either from code or visit the UI through
   * admin/structure/ds/emergency.
   */
  public static function isDisabled() {
    if (\Drupal::state()->get('ds.disabled', FALSE)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Gets all Display Suite field layouts options.
   *
   * Mainly used by select fields.
   *
   * @return array
   *   List of field layouts.
   */
  public static function getFieldLayoutOptions() {
    $options = array();
    foreach (\Drupal::service('plugin.manager.ds.field.layout')->getDefinitions() as $plugin_id => $plugin) {
      $options[$plugin_id] = $plugin['title'];
    }
    return $options;
  }

  /**
   * Utility function to return CSS classes.
   */
  public static function getClasses($name = 'region') {
    static $classes = array();

    if (!isset($classes[$name])) {
      $classes[$name] = array();
      $custom_classes = \Drupal::config('ds.settings')->get('classes.' . $name);
      if (!empty($custom_classes)) {
        $classes[$name][''] = t('None');
        foreach ($custom_classes as $value) {
          $classes_splitted = explode("|", $value);
          $key = trim($classes_splitted[0]);
          $friendly_name = isset($classes_splitted[1]) ? trim($classes_splitted[1]) : $key;
          $classes[$name][Html::escape($key)] = $friendly_name;
        }
      }
      // Prevent the name from being changed.
      $name_clone = $name;
      \Drupal::moduleHandler()->alter('ds_classes', $classes[$name], $name_clone);
    }

    return $classes[$name];
  }

}
