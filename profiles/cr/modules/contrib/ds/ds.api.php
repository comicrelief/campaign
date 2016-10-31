<?php

/**
 * @file
 * Hooks provided by Display Suite module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Modify the list of available ds field plugins.
 *
 * This hook may be used to modify plugin properties after they have been
 * specified by other modules.
 *
 * @param array $plugins
 *   An array of all the existing plugin definitions, passed by reference.
 *
 * @see \Drupal\views\Plugin\DsPluginManager
 */
function hook_ds_fields_info_alter(&$plugins) {
  $plugins['node_title']['title'] = t('My title');
}

/**
 * Modify the list of available ds field template plugins.
 *
 * This hook may be used to modify plugin properties after they have been
 * specified by other modules.
 *
 * @param array $plugins
 *   An array of all the existing plugin definitions, passed by reference.
 *
 * @see \Drupal\views\Plugin\DsFieldTemplatePluginManager
 */
function hook_ds_field_templates_info_alter(&$plugins) {
  $plugins['expert']['title'] = t('My template');
}

/**
 * Return configuration summary for the field format.
 *
 * As soon as you have hook_ds_fields and one of the fields
 * has a settings key, Display Suite will call this hook for the summary.
 *
 * @param array $field
 *   The configuration of the field.
 *
 * @return string
 *   The summary to show on the Field UI.
 */
function hook_ds_field_format_summary($field) {
  return 'Field summary';
}

/**
 * Modify the layout settings just before they get saved.
 *
 * @param array $record
 *   The record just before it gets saved into the database.
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The form_state values.
 */
function hook_ds_layout_settings_alter($record, \Drupal\Core\Form\FormStateInterface $form_state) {
  $record['layout']['settings']['classes'] = array('layout-class');
}

/**
 * Alter the layout render array.
 *
 * @param array $layout_render_array
 *   The render array.
 * @param array $context
 *   An array with the context that is being rendered. Available keys are
 *   - entity
 *   - entity_type
 *   - bundle
 *   - view_mode
 * @param array $vars
 *   All variables available for render. You can use this to add css classes.
 */
function hook_ds_pre_render_alter(&$layout_render_array, $context, &$vars) {
  $layout_render_array['left'][] = array('#markup' => 'cool!', '#weight' => 20);
  $vars['attributes']['class'][] = 'custom';
}

/**
 * Alter the region options in the field UI screen.
 *
 * This function is only called when a layout has been chosen.
 *
 * @param array $context
 *   A collection of keys for the context. The keys are 'entity_type',
 *   'bundle' and 'view_mode'.
 * @param array $region_info
 *   A collection of info for regions. The keys are 'region_options'
 *   and 'table_regions'.
 */
function hook_ds_layout_region_alter($context, &$region_info) {
  $region_info['region_options']['my_region'] = 'New region';
  $region_info['table_regions']['my_region'] = array(
    'title' => \Drupal\Component\Utility\Html::escape('New region'),
    'message' => t('No fields are displayed in this region'),
  );
}

/**
 * Alter the field label options.
 *
 * Note that you will either update the preprocess functions or the
 * field.html.twig file when adding new options.
 *
 * @param array $field_label_options
 *   A collection of field label options.
 */
function hook_ds_label_options_alter(&$field_label_options) {
  $field_label_options['label_after'] = t('Label after field');
}

/**
 * Alter the view mode just before it's rendered by the DS views entity plugin.
 *
 * @param string $view_mode
 *   The name of the view mode.
 * @param array $context
 *   A collection of items which can be used to identify in what
 *   context an entity is being rendered. The variable contains 3 keys:
 *     - entity: The entity being rendered.
 *     - view_name: the name of the view.
 *     - display: the name of the display of the view.
 */
function hook_ds_views_view_mode_alter(&$view_mode, $context) {
  if ($context['view_name'] == 'my_view_name') {
    $view_mode = 'new_view_mode';
  }
}

/**
 * Theme an entity through an advanced function.
 *
 * The function is coming from the views entity plugin.
 *
 * @param \Drupal\Core\Entity\EntityInterface $entity
 *   The entity.
 * @param string $view_mode
 *   The name of the view mode.
 *
 * @return array
 *   The rendered entity
 */
function hook_ds_views_row_render_entity(\Drupal\Core\Entity\EntityInterface $entity, $view_mode) {
  $entity = Drupal\ds\Plugin\DsField\Node::load(1);
  return \Drupal::entityManager()->getViewBuilder('node')->view($entity, $view_mode);
}

/**
 * Theme an entity through an advanced function.
 *
 * Function is coming from the views entity plugin.
 *
 * @param array $build
 *   The builded entity.
 * @param array $context
 *   Collection of parameters (row, view and view_mode).
 */
function hook_ds_views_row_render_entity_alter(&$build, $context) {
  // You can do whatever you want to here.
  $build['data'] = array(
    '#markup' => 'Sample text',
    '#weight' => 20,
  );
}

/**
 * Allow modules to provide additional classes for regions and layouts.
 */
function hook_ds_classes_alter(&$classes, $name) {
  if ('ds_classes_regions' === $name) {
    $classes['css-class-name'] = t('Custom Styling');
  }
}

/**
 * Allow modules to alter the operations on the dynamic field overview page.
 */
function hook_ds_field_operations_alter(&$operations, $field) {
  if ($field['type'] == 'block') {
    unset($operations['edit']);
  }
}

/**
 * @} End of "addtogroup hooks".
 */
