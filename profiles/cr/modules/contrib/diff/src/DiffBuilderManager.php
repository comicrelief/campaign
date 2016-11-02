<?php

namespace Drupal\diff;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Plugin type manager for field diff builders.
 *
 * @ingroup field_diff_builder
 */
class DiffBuilderManager extends DefaultPluginManager {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Wrapper object for writing/reading simple configuration from diff.settings.yml
   */
  protected $config;

  /**
   * Wrapper object for writing/reading simple configuration from diff.plugins.yml
   */
  protected $pluginsConfig;

  /**
   * Static cache of field definitions per bundle and entity type.
   *
   * @var array
   */
  protected $pluginDefinitions;

  /**
   * Constructs a DiffBuilderManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory) {
    parent::__construct('Plugin/diff/Field', $namespaces, $module_handler, '\Drupal\diff\FieldDiffBuilderInterface', 'Drupal\diff\Annotation\FieldDiffBuilder');

    $this->setCacheBackend($cache_backend, 'field_diff_builder_plugins');
    $this->alterInfo('field_diff_builder_info');
    $this->entityTypeManager = $entity_type_manager;
    $this->config = $config_factory->get('diff.settings');
    $this->pluginsConfig =  $config_factory->get('diff.plugins');

  }

  /**
   * Define whether a field should be displayed or not as a diff change.
   *
   * To define if a field should be displayed in the diff comparison, check if
   * it is revisionable and is not the bundle or revision field of the entity.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $field_storage_definition
   *   The field storage definition.
   *
   * @return bool
   *   TRUE if the field will be displayed.
   */
  public function showDiff(FieldStorageDefinitionInterface $field_storage_definition) {
    $show_diff = FALSE;
    // Check if the field is revisionable.
    if ($field_storage_definition->isRevisionable()) {
      $show_diff = TRUE;
      // Do not display the field, if it is the bundle or revision field of the
      // entity.
      $entity_type = $this->entityTypeManager->getDefinition($field_storage_definition->getTargetEntityTypeId());
      // @todo Don't hard code fields after: https://www.drupal.org/node/2248983
      if (in_array($field_storage_definition->getName(), ['revision_log', 'revision_uid' , $entity_type->getKey('bundle'), $entity_type->getKey('revision')])) {
        $show_diff = FALSE;
      }
    }
    return $show_diff;
  }

  /**
   * Creates a plugin instance for a field definition.
   *
   * Creates the instance based on the selected plugin for the field.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   * @param string $bundle
   *   (optional) The entity bundle where to check form display when selecting
   *   the plugin for a field.
   *
   * @return object
   *   The plugin instance, NULL if none.
   */
  public function createInstanceForFieldDefinition(FieldDefinitionInterface $field_definition, $bundle = NULL) {
    $plugin = NULL;
    $selected_plugin = $this->getSelectedPluginForFieldDefinition($field_definition, $bundle);
    if ($selected_plugin && $selected_plugin['type'] != 'hidden') {
      if (!empty($selected_plugin['settings'])) {
        $plugin = $this->createInstance($selected_plugin['type'], $selected_plugin['settings']);
      }
      else {
        $plugin = $this->createInstance($selected_plugin['type'], []);
      }
    }
    return $plugin;
  }

  /**
   * Selects a default plugin for a field definition.
   *
   * Checks the display configuration of the field to define if it should be
   * displayed in the diff comparison.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   * @param string $bundle
   *   (optional) The entity bundle where to check the form display if no
   *   setting is set for a field.
   *
   * @return array
   *   The plugin instance, NULL if none.
   */
  public function getSelectedPluginForFieldDefinition(FieldDefinitionInterface $field_definition, $bundle = NULL) {
    $selected_plugin = NULL;
    $visible = TRUE;
    $field_key = $field_definition->getFieldStorageDefinition()->getTargetEntityTypeId() . '.' . $field_definition->getName();
    // Do not check the entity form display if there are plugins settings stored
    // for the current field.
    if ($this->pluginsConfig->get('fields.' . $field_key)) {
      $selected_plugin = $this->getSelectedPluginForFieldStorageDefinition($field_definition->getFieldStorageDefinition());
    }
    else {
      // If entity is set load its form display settings.
      if ($bundle) {
        $storage = $this->entityTypeManager->getStorage('entity_form_display');
        if ($display = $storage->load($field_definition->getTargetEntityTypeId() . '.' . $bundle . '.default')) {
          $visible = (bool) $display->getComponent($field_definition->getName());
        }
      }
      if ($visible) {
        $selected_plugin = $this->getSelectedPluginForFieldStorageDefinition($field_definition->getFieldStorageDefinition());
      }
    }
    return $selected_plugin;
  }

  /**
   * Selects a default plugin for a field storage definition.
   *
   * Checks if a plugin has been already selected for the field, otherwise
   * chooses one between the plugins that can be applied to the field.
   *
   * @param FieldStorageDefinitionInterface $field_definition
   *   The field storage definition.
   *
   * @return array
   *   The selected plugin for the field.
   */
  public function getSelectedPluginForFieldStorageDefinition(FieldStorageDefinitionInterface $field_definition) {
    $plugin_options = $this->getApplicablePluginOptions($field_definition);
    $field_key = $field_definition->getTargetEntityTypeId() . '.' . $field_definition->getName();
    $selected_plugin = $this->pluginsConfig->get('fields.' . $field_key);
    // Check if the plugin stored to the fields is still applicable.
    if (!$selected_plugin || !in_array($selected_plugin['type'], array_keys($plugin_options))) {
      if (!empty($plugin_options)) {
        $selected_plugin['type'] = array_keys($plugin_options)[0];
      }
      else {
        $selected_plugin['type'] = 'hidden';
      }
    }
    return $selected_plugin;
  }

  /**
   * Gets the applicable plugin options for a given field.
   *
   * Loop over the plugins that can be applied to the field and builds an array
   * of possible plugins based on each plugin weight.
   *
   * @param FieldStorageDefinitionInterface $field_definition
   *   The field storage definition.
   *
   * @return array
   *   The plugin option for the given field based on plugin weight.
   */
  public function getApplicablePluginOptions(FieldStorageDefinitionInterface $field_definition) {
    $plugins = $this->getPluginDefinitions();
    // Build a list of all diff plugins supporting the field type of the field.
    $plugin_options = [];
    if (isset($plugins[$field_definition->getType()])) {
      // Sort the plugins based on their weight.
      uasort($plugins[$field_definition->getType()], 'Drupal\Component\Utility\SortArray::sortByWeightElement');
      foreach ($plugins[$field_definition->getType()] as $id => $weight) {
        $definition = $this->getDefinition($id, FALSE);
        // Check if the plugin is applicable.
        if (isset($definition['class']) && in_array($field_definition->getType(), $definition['field_types']) && $definition['class']::isApplicable($field_definition)) {
          $plugin_options[$id] = $this->getDefinitions()[$id]['label'];
        }
      }
    }
    return $plugin_options;
  }

  /**
   * Initializes the local pluginDefinitions property.
   *
   * Loop over the plugin definitions and build an array keyed by the field type
   * that plugins can be applied to.
   *
   * @return array
   *   The initialized plugins array sort by field type.
   */
  public function getPluginDefinitions() {
    if (!isset($this->pluginDefinitions)) {
      // Get the definition of all the FieldDiffBuilder plugins.
      foreach ($this->getDefinitions() as $plugin_definition) {
        if (isset($plugin_definition['field_types'])) {
          // Iterate through all the field types this plugin supports
          // and for every such field type add the id of the plugin.
          if (!isset($plugin_definition['weight'])) {
            $plugin_definition['weight'] = 0;
          };

          foreach ($plugin_definition['field_types'] as $id) {
            $this->pluginDefinitions[$id][$plugin_definition['id']]['weight'] = $plugin_definition['weight'];
          }
        }
      }
    }
    return $this->pluginDefinitions;
  }

  /**
   * Clear the pluginDefinitions local property array.
   */
  public function clearCachedDefinitions() {
    unset($this->pluginDefinitions);
  }
}
