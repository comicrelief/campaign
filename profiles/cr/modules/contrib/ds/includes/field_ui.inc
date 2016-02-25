<?php

/**
 * @file
 * Field UI functions for Display Suite.
 */

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\Display\EntityDisplayInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;
use Drupal\ds\Ds;
use Drupal\ds\Plugin\DsField\DsFieldInterface;
use Drupal\field\FieldConfigInterface;
use Drupal\field_ui\FieldUI;
use Drupal\layout_plugin\Layout;
use Drupal\layout_plugin\Plugin\Layout\LayoutInterface;

/**
 * Adds the Display Suite fields and layouts to the form.
 */
function  ds_field_ui_fields_layouts(&$form, FormStateInterface $form_state) {
  global $base_root, $base_path;

  // Get the entity_type, bundle and view mode.
  $entity_type = $form['#entity_type'];
  $bundle = $form['#bundle'];

  /** @var EntityFormInterface $entity_form */
  $entity_form = $form_state->getFormObject();
  /** @var EntityDisplayInterface $entity_display */
  $entity_display = $entity_form->getEntity();
  $view_mode = $entity_display->getMode();

  // Create vertical tabs.
  ds_field_ui_create_vertical_tabs($form);

  // Add layout fieldset.
  _ds_field_ui_table_layouts($entity_type, $bundle, $view_mode, $form, $form_state);

  // Add/alter fields on the table, but only if a layout is selected.
  if (!empty($form['#ds_layout'])) {
    _ds_field_ui_fields($entity_type, $bundle, $view_mode, $form, $form_state);

    // Also alter core fields
    _ds_field_ui_core_fields($form, $form_state);
  }

  // Special validate function for field group.
  if ($form_state->has('no_field_group')) {
    array_unshift($form['#validate'], '_ds_field_group_field_ui_fix_notices');
  }

  // Attach js.
  $form['#attached']['library'][] = 'ds/admin';

  // Add process function to add the regions.
  $form['#process'][] = 'ds_field_ui_regions';

  // Add a destination so we can get back if layout has been changed.
  $form['ds_source'] = array(
    '#type' => 'hidden',
    '#value' => $base_root . $base_path,
  );
  $form['ds_destination'] = array(
    '#type' => 'hidden',
    '#value' => drupal_get_destination(),
  );
  $form['ds_entity_type'] = array(
    '#type' => 'hidden',
    '#value' => $entity_type,
  );
  $form['ds_bundle'] = array(
    '#type' => 'hidden',
    '#value' => $bundle,
  );
  $form['ds_view_mode'] = array(
    '#type' => 'hidden',
    '#value' => $view_mode,
  );
}

/**
 * Create vertical tabs.
 */
function ds_field_ui_create_vertical_tabs(&$form) {

  // Add additional settings vertical tab.
  if (!isset($form['additional_settings'])) {
    $form['additional_settings'] = array(
      '#type' => 'vertical_tabs',
      '#theme_wrappers' => array('vertical_tabs'),
      '#prefix' => '<div>',
      '#suffix' => '</div>',
      '#tree' => TRUE,
    );
  }

  // @todo needs core permission
  $view_mode_admin_access = \Drupal::currentUser()->hasPermission('admin_view_modes');
  if (isset($form['modes'])) {
    $form['modes']['#group'] = 'additional_settings';
    $form['modes']['#weight'] = -10;
    if ($view_mode_admin_access) {
      $url = \Drupal\Core\Url::fromRoute('field_ui.display_mode');
      $form['modes']['view_modes_custom']['#description'] = \Drupal::l(t('Manage display modes'), $url);
    }
  }
}

/**
 * Add Regions to 'Manage fields' or 'Manage display' screen.
 *
 * @param $form
 *   The form to add layout fieldset and extra Display Suite fields.
 * @param $form_state
 *   The current form state.
 */
function ds_field_ui_regions($form, FormStateInterface $form_state) {

  // Get the entity_type, bundle and view mode.
  $entity_type = $form['#entity_type'];
  $bundle = $form['#bundle'];

  /** @var EntityFormInterface $entity_form */
  $entity_form = $form_state->getFormObject();
  /** @var EntityDisplayInterface $entity_display */
  $entity_display = $entity_form->getEntity();
  $view_mode = $entity_display->getMode();

  // Ignore field_group options.
  if ($form_state->has('no_field_group')) {
    unset($form['fields']['_add_new_group']);
    $form['field_group']['#access'] = FALSE;
  }

  // Check layout.
  $layout = isset($form['#ds_layout']) ? $form['#ds_layout'] : FALSE;

  // Build an array which keys are the field names and
  // values are the region they are rendered in.
  $field_regions = array();

  // Change UI to add Region column if we have a layout.
  if ($layout) {
    foreach ($layout['regions'] as $region_name => $field_names) {
      foreach ($field_names as $field_name) {
        $field_regions[$field_name] = $region_name;
      }
    }

    $table = &$form['fields'];

    $table['#header'] = array(
      t('Label'),
      t('Weight'),
      t('Parent'),
      t('Region'),
      t('Name'),
      t('Field'),
      t('Widget'),
      array('data' => t('Operations'), 'colspan' => 2),
    );

    $table['#regions'] = array();
    $region_options = array();
    foreach ($layout['region_names'] as $region_key => $region_info) {
      $region_options[$region_key] = $region_info['label'];
      $table['#regions'][$region_key] = array(
        'title' => $region_info['label'],
        'message' => t('No fields are displayed in this region'),
      );
    }

    // Let other modules alter the regions.
    $context = array(
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'view_mode' => $view_mode
    );
    $region_info = array(
      'region_options' => &$region_options,
      'table_regions' => &$table['#regions'],
    );
    \Drupal::moduleHandler()->alter('ds_layout_region', $context, $region_info);

    $region_options['hidden'] = t('Disabled');
    $table['#regions']['hidden'] = array(
      'title' => t('Disabled'),
      'message' => t('No fields are hidden.')
    );

    $region = array(
      '#type' => 'select',
      '#options' => $region_options,
      '#default_value' => 'hidden',
      '#attributes' => array(
        'class' => array('ds-field-region'),
      )
    );

    // Update existing rows by changing rowHandler and adding regions.
    foreach (Element::children($table) as $name) {
      $row = &$table[$name];
      $row['#js_settings'] = array('rowHandler' => 'ds');
      $row['#region_callback'] = 'ds_field_ui_row_region';

      // Remove hidden format.
      if (isset($row['plugin']['type']['#options']['hidden'])) {
        unset($row['plugin']['type']['#options']['hidden']);
      }

      // Add label class.
      if (isset($row['label'])) {
        if ($form_state->has('plugin_settings')) {
          $plugin_settings = $form_state->get('plugin_settings');
          if (isset($plugin_settings[$name]['ft']['settings']) && !empty($plugin_settings[$name]['ft']['settings']['lb'])) {
            $row['human_name']['#plain_text'] = $plugin_settings[$name]['ft']['settings']['lb'] . ' ' . t('(Original: @orig)', array('@orig' => $row['human_name']['#plain_text']));
          }
        }
      }

      // Add region.
      $split = 7;
      $second = array_splice($row, $split);
      $row['region'] = $region;
      $row['region']['#default_value'] = (isset($field_regions[$name]) && isset($region_options[$field_regions[$name]])) ? $field_regions[$name] : 'hidden';
      $row = array_merge($row, $second);
    }
  }

  return $form;
}

/**
 * Returns the region to which a row in the Field UI screen belongs.
 *
 * @param $row
 *   The current row that is being rendered in the Field UI screen.
 *
 * @return string
 *   The region.
 */
function ds_field_ui_row_region($row) {
  return isset($row['region']['#value']) ? $row['region']['#value'] : 'hidden';
}

/**
 * Validate the layout settings on the Field UI.
 */
function ds_field_ui_layouts_validate($form, FormStateInterface $form_state) {
  // Determine layout variables.
  $layout = $form_state->getValue('layout');
  $old_layout = $form_state->getValue('old_layout');
  $new_layout = ($layout != $old_layout) || empty($old_layout);

  // Only validate the layout settings if the layout hasn't changed.
  if (!$new_layout && !empty($layout)) {
    /** @var LayoutInterface $layout_plugin */
    $layout_plugin = Layout::layoutPluginManager()->createInstance($form_state->getValue('layout'), []);
    $layout_form = isset($form['layout_configuration']) ? $form['layout_configuration'] : [];
    foreach (Element::children($form) as $name) {
      if (!empty($form[$name]['#ds_layout_configuration'])) {
        $layout_form[$name] = $form[$name];
      }
    }
    $layout_form_state = (new FormState())->setValues($form_state->getValue('layout_configuration', []));
    $layout_plugin->validateConfigurationForm($layout_form, $layout_form_state);
  }

  // Move the view modes so Field UI can handle them.
  if ($form_state->hasValue('modes')) {
    $modes = $form_state->getValue('modes');
    if (isset($modes['display_modes_custom'])) {
      $form_state->setValue('display_modes_custom', $modes['display_modes_custom']);
    }
  }
}

/**
 * Save the layout settings from the 'Manage display' screen.
 */
function ds_field_ui_layouts_save($form, FormStateInterface $form_state) {
  $save_display = FALSE;

  // Get default values.
  $entity_type = $form['#entity_type'];

  /** @var EntityFormInterface $entity_form */
  $entity_form = $form_state->getFormObject();

  // Get the entity display.
  /** @var EntityDisplayInterface $display */
  $display = $entity_form->getEntity();

  // Get view mode
  $view_mode = $display->getMode();

  // Determine layout variables.
  $layout = $form_state->getValue('layout');
  $disable_css = $form_state->getValue('disable_css');
  $old_layout = $form_state->getValue('old_layout');
  $new_layout = ($layout != $old_layout) || empty($old_layout);

  $key = array_search('ds_field_ui_change_layout_submit', $form['actions']['submit']['#submit']);
  if ($key && !empty($old_layout)) {
    return;
  }


  $ds_layouts = Ds::getLayouts();

  // Save layout and add regions if necessary.
  $record = array();
  $record['layout'] = array(
    'id' => $layout,
    'path' =>  !empty($layout) ? $ds_layouts[$layout]['path'] : '',
    'library' => isset($ds_layouts[$layout]['library']) ? $ds_layouts[$layout]['library'] : FALSE,
    'disable_css' => $disable_css ? TRUE : FALSE,
  );
  $record['regions'] = array();

  // Remove old layout if necessary.
  if ($new_layout && !empty($old_layout) || empty($layout)) {
    $display->unsetThirdPartySetting('ds', 'layout');
    $display->unsetThirdPartySetting('ds', 'regions');
    $display->save();
  }

  if ($new_layout && !empty($layout)) {
    $save_display = TRUE;

    // Move current visible fields into a default region, so
    // we keep their current settings.
    $layouts = Ds::getLayouts();
    $sl = $layouts[$layout];
    $first_region = key($sl['regions']);
    $record['layout']['settings']['classes'] = array();
    $record['layout']['settings']['wrappers'] = array();
    $record['layout']['settings']['outer_wrapper'] = 'div';
    $record['layout']['settings']['attributes'] = '';
    $record['layout']['settings']['link_attribute'] = FALSE;
    $record['layout']['settings']['link_custom'] = '';
    $fields = _ds_sort_fields($form_state->getValue('fields'), 'weight');
    foreach ($fields as $field_key => $field) {

      // Ignore new fieldgroup, new field or existing field.
      if (in_array($field_key, array('_add_new_field', '_add_existing_field', '_add_new_group'))) {
        continue;
      }

      // Can either be form or display.
      if ((isset($field['type']) && $field['type'] != 'hidden')) {
        $record['regions'][$first_region][] = $field_key;
      }
    }
    // In case this is the full node view mode and if the comment module
    // is enabled for this content type, add it as well.
    if ($entity_type == 'node' && $view_mode == 'full' && \Drupal::moduleHandler()->moduleExists('comment')) {
      $record['regions'][$first_region][] = 'comments';
    }
  }
  // Update existing layout.
  elseif (!empty($layout)) {
    $save_display = TRUE;

    $fields = _ds_sort_fields($form_state->getValue('fields'), 'weight');

    foreach ($fields as $key => $field) {
      // Make sure we need to save anything for this field.
      if ($field['region'] == 'hidden') {
        continue;
      }

      if (!isset($record['regions'][$field['region']])) {
        $record['regions'][$field['region']] = array();
      }
      $record['regions'][$field['region']][] = $key;
    }

    /** @var LayoutInterface $layout_plugin */
    $layout_plugin = Layout::layoutPluginManager()->createInstance($layout, []);
    $layout_form = isset($form['layout_configuration']) ? $form['layout_configuration'] : [];
    foreach (Element::children($form) as $name) {
      if (!empty($form[$name]['#ds_layout_configuration'])) {
        $layout_form[$name] = $form[$name];
      }
    }
    $layout_form_state = (new FormState())->setValues($form_state->getValue('layout_configuration', []));
    $layout_plugin->submitConfigurationForm($layout_form, $layout_form_state);

    // Get the layout settings from the layout_plugin.
    $record['layout']['settings'] = $layout_plugin->getConfiguration();

    // Let other modules alter the layout settings.
    \Drupal::moduleHandler()->alter('ds_layout_settings', $record, $form_state);
  }

  // Save the configuration.
  if ($save_display) {
    // Let other modules alter the layout settings.
    \Drupal::moduleHandler()->alter('ds_layout_settings', $record, $form_state);

    foreach (array_keys($record) as $key) {
      $display->setThirdPartySetting('ds', $key, $record[$key]);
    }
    $display->save();
  }
}

/**
 * Form validation handler for _ds_field_ui_fields().
 */
function ds_field_ui_fields_validate($form, FormStateInterface $form_state) {
  $fields = $form_state->getValue('fields');
  foreach (Element::children($form['fields']) as $key) {
    if (isset($fields[$key]['settings_edit_form']['settings'])) {
      $settings = $fields[$key]['settings_edit_form']['settings'];
      if (!empty($settings)) {
        $plugin_settings = $form_state->get('plugin_settings');
        $plugin_settings[$key] = $settings;
        $form_state->set('plugin_settings', $plugin_settings);
      }
    }

    if (isset($fields[$key]['settings_edit_form']['third_party_settings']['ds'])) {
      $settings = $fields[$key]['settings_edit_form']['third_party_settings']['ds'];
      if (!empty($settings)) {
        $plugin_settings = $form_state->get('plugin_settings');
        $plugin_settings[$key] = $settings;
        $form_state->set('plugin_settings', $plugin_settings);
      }
    }
  }
}

/**
 * Save the field settings from the 'Manage display' screen.
 */
function ds_field_ui_fields_save($form, FormStateInterface $form_state) {
  if (empty($form['#ds_fields'])) {
    return;
  }

  // Get the entity display.
  /** @var EntityFormInterface $entity_form */
  $entity_form = $form_state->getFormObject();
  /** @var EntityDisplayInterface $display */
  $display = $entity_form->getEntity();

  $field_settings = array();

  // Fetch values from form_state.
  $all_field_values = $form_state->getValue('fields');
  $all_plugin_settings = $form_state->get('plugin_settings');

  // Save settings for each Display Suite field.
  $ds_fields = $form['#ds_fields'];
  foreach ($ds_fields as $field) {

    $field_values = $all_field_values[$field];

    // In case the region is hidden, do not save.
    if (isset($field_values['region']) && $field_values['region'] == 'hidden') {
      continue;
    }

    // Build settings.
    $settings = array();
    $settings['plugin_id'] = $field;
    $settings['weight'] = $field_values['weight'];
    $settings['label'] = $field_values['label'];
    $settings['formatter'] = $field_values['plugin']['type'];

    // Any formatter settings.
    if (isset($all_plugin_settings[$field]['settings'])) {
      $settings['settings'] = $all_plugin_settings[$field]['settings'];
    }
    elseif (isset($all_plugin_settings[$field])) {
      // When the settings for that fields aren't changed the structure is different.
      // @todo figure out how we can fix this
      $settings['settings'] = $all_plugin_settings[$field];
    }

    $field_settings[$field] = $settings;

    // Always unset the field template settings from the regular settings array
    // @todo needs serious review
    unset($field_settings[$field]['settings']['ft']);

    // Add field template plugin settings if provided.
    $values = isset($all_plugin_settings[$field]['ft']) ? $all_plugin_settings[$field]['ft'] : array();

    if (!empty($values)) {
      $field_settings[$field]['ft'] = $values;

      $default_field_function = \Drupal::config('ds.settings')->get('ft-default');
      $function = isset($values['id']) ? $values['id'] : $default_field_function;
      $field_settings[$field]['ft']['id'] = $function;

      // It's important for schema validation to never save empty arrays
      if (empty($field_settings[$field]['ft']['settings'])) {
        unset($field_settings[$field]['ft']['settings']);
      }
    }

    // Last but not least unset the settings if they are empty after moving the field template settings
    if (empty($field_settings[$field]['settings'])) {
      unset($field_settings[$field]['settings']);
    }
  }

  // Save the record.
  $display->unsetThirdPartySetting('ds', 'fields');
  if (!empty($field_settings)) {
    $display->setThirdPartySetting('ds', 'fields', $field_settings);
  }
  $display->save();

  // Clear the ds_fields cache.
  Cache::invalidateTags(array('ds_fields_info'));
}

/**
 * Clone a fields layout.
 */
function ds_field_ui_layout_clone($form, FormStateInterface $form_state) {
  $clone = $form_state->getValue('clone');
  list(,,$cv) = explode('.', $clone);
  $entity_type = $form['#entity_type'];
  $bundle = $form['#bundle'];

  /** @var EntityFormInterface $entity_form */
  $entity_form = $form_state->getFormObject();

  // Get the entity display.
  /** @var EntityDisplayInterface $old_display */
  $old_display = $entity_form->getEntity();
  $view_mode = $old_display->getMode();
  $old_display->delete();

  /** @var EntityViewDisplayInterface $display */
  $display = EntityViewDisplay::load($entity_type . '.' . $bundle . '.' . $cv);

  $clone_display = $display->createCopy($view_mode);
  $clone_display->save();

  // Show message.
  drupal_set_message(t('The layout has been cloned.'));
}

/**
 * Creates a summary for the field format configuration summary.
 *
 * @param $field
 *   The configuration of the field.
 *
 * @return array $summary
 *   The summary
 */
function ds_field_settings_summary(DsFieldInterface $plugin_instance, $settings, FormStateInterface $form_state) {
  // Create the form
  $summary = $plugin_instance->settingsSummary($settings);

  // Add field template summary
  ds_field_formatter_settings_summary_alter($summary, array('field_definition' => $plugin_instance, 'form_state' => $form_state));

  if (empty($summary)) {
    return array();
  }

  return array(
    '#type' => 'inline_template',
    '#template' => '<div class="field-plugin-summary">{{ summary|safe_join("<br />") }}</div>',
    '#context' => array('summary' => $summary),
    '#cell_attributes' => array('class' => array('field-plugin-summary-cell')),
  );
}

/**
 * Creates a form for Display Suite fields.
 * .
 * @param $field
 *   The field definition.
 * @param FormStateInterface $form_state
 *   The form_state
 *
 * @return mixed $form
 */
function ds_field_settings_form(DsFieldInterface $plugin_instance, FormStateInterface $form_state) {
  // Create the form
  $form = $plugin_instance->settingsForm(array(), $form_state);

  // Add field template settings to every field if enabled.
  if (\Drupal::config('ds.settings')->get('field_template')) {
    $context = array(
      'instance' => array(
        'entity_type' => $plugin_instance->getEntityTypeId(),
        'bundle' => $plugin_instance->bundle(),
        'field_name' => $plugin_instance->getName(),
      ),
      'view_mode' => $plugin_instance->viewMode(),
    );

    ds_field_template_settings_form($form, $form_state, $context);
  }

  return $form;
}

/**
 * Add fake field group value in.
 */
function _ds_field_group_field_ui_fix_notices($form, FormStateInterface $form_state) {
  $field_group = array(
    'group_name' => '',
    'label' => '',
  );
  $fields = $form_state->getValue('fields');
  $fields['_add_new_group'] = $field_group;
  $form_state->setValue('fields', $fields);
}

/**
 * Add the layouts fieldset on the Field UI screen.
 *
 * @param $entity_type
 *   The name of the entity type.
 * @param $bundle
 *   The name of the bundle
 * @param $view_mode
 *   The name of the view_mode
 * @param $form
 *   A collection of form properties.
 * @param FormStateInterface $form_state
 *   The form_state
 */
function _ds_field_ui_table_layouts($entity_type, $bundle, $view_mode, &$form, FormStateInterface $form_state) {
  $ds_layouts = Ds::getLayouts();
  $layout_options = array('' => t('- None -'));
  $optgroup = '';
  foreach ($ds_layouts as $key => $layout_definition) {
    $optgroup = t('Other');

    // Create new layout option group.
    if (!empty($layout_definition['category'])) {
      $optgroup = (string)$layout_definition['category'];
    }

    if (!isset($layout_options[$optgroup])) {
      $layout_options[$optgroup] = array();
    }

    // Stack the layout.
    $layout_options[$optgroup][$key] = $layout_definition['label'];
  }

  // If there is only one $optgroup, move it to the root.
  if (count($layout_options) == 2) {
    $options = $layout_options[$optgroup];
    $layout_options = array_merge(array('' => t('- None -')), $options);
  }

  // Add layouts form.
  $form['ds_layouts'] = array(
    '#type' => 'details',
    '#title' => t('Layout for @bundle in @view_mode', array('@bundle' => str_replace('_', ' ', $bundle), '@view_mode' => str_replace('_', ' ', $view_mode))),
    '#collapsible' => TRUE,
    '#group' => 'additional_settings',
    '#collapsed' => FALSE,
    '#weight' => -100,
  );

  // @todo cleanup
  $layout = array();
  /** @var EntityFormInterface $entity_form */
  $entity_form = $form_state->getFormObject();
  /** @var EntityViewDisplayInterface $display */
  $display = $entity_form->getEntity();
  if ($display->getThirdPartySetting('ds', 'layout')) {
    $layout_configuration = $display->getThirdPartySetting('ds', 'layout');
    $layout = $ds_layouts[$layout_configuration['id']];
    $layout['layout'] = $layout_configuration['id'];
    $layout['disable_css'] = $layout_configuration['disable_css'];
    $layout['settings'] = $layout_configuration['settings'] ?: [];
    $layout['regions'] = $display->getThirdPartySetting('ds', 'regions');
    $layout['fields'] = $display->getThirdPartySetting('ds', 'fields');
  }

  if (!empty($layout) && isset($layout['layout']) && isset($ds_layouts[$layout['layout']])) {
    $layout['region_names'] = $ds_layouts[$layout['layout']]['regions'];
    $form['#ds_layout'] = $layout;
  }

  // Load the layout preview form
  $layout['layout_options'] = $layout_options;
  _ds_field_ui_table_layouts_preview($form, $form_state, $ds_layouts, $layout, $entity_type, $bundle, $view_mode);

  if (!empty($layout) && !empty($layout['settings'])) {
    /** @var LayoutInterface $layout_plugin */
    $layout_plugin = Layout::layoutPluginManager()->createInstance($layout['layout'], $layout['settings'] ?: []);

    $layout_configuration_form = $layout_plugin->buildConfigurationForm([], $form_state);

    // Merge 'details' elements in 'additional_settings' group into the main
    // form.
    foreach (Element::children($layout_configuration_form) as $name) {
      $element = $layout_configuration_form[$name];
      if ($element['#type'] == 'details' && $element['#group'] == 'additional_settings') {
        $form[$name] = $layout_configuration_form[$name];
        unset($layout_configuration_form[$name]);

        $form[$name]['#ds_layout_configuration'] = TRUE;
        $form[$name]['#parents'] = ['layout_configuration', $name];
        $form[$name]['#tree'] = TRUE;

        if ($layout['layout'] === 'ds_reset') {
          $form[$name]['#access'] = FALSE;
        }
      }
    }

    // If anything is left, then we put it on it's own vertial tab.
    if (!Element::isEmpty($layout_configuration_form)) {
      $form['layout_configuration'] = array_merge($layout_configuration_form, [
        '#group' => 'additional_settings',
        '#type' => 'details',
        '#title' => t('Layout settings'),
        '#tree' => TRUE,
      ]);
    }
  }
  else {
    // See if we can clone from another view mode.
    $options = array();
    $entity_displays = \Drupal::configFactory()->listAll('core.entity_view_display.' . $entity_type . '.' . $bundle);
    foreach ($entity_displays as $name) {
      $row = \Drupal::config($name)->get();
      if ($row['mode'] == $view_mode) {
        continue;
      }
      if ($row['targetEntityType'] == $entity_type && $row['bundle'] == $bundle) {
        $options[$row['id']] = Unicode::ucfirst(str_replace('_', ' ', $row['targetEntityType'])) . ' > ' . Unicode::ucfirst(str_replace('_', ' ', $row['bundle'])) . ' > ' . Unicode::ucfirst(str_replace('_', ' ', $row['mode']));
      }
    }

    if (!empty($options)) {

      // Clone from another layout.
      $form['ds_clone'] = array(
        '#type' => 'details',
        '#group' => 'additional_settings',
        '#title' => t('Clone layout'),
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
      );
      $form['ds_clone']['clone'] = array(
        '#title' => t('Select an existing layout to clone.'),
        '#type' => 'select',
        '#options' => $options,
        '#weight' => 20,
      );
      $form['ds_clone']['clone_submit'] = array(
        '#type' => 'submit',
        '#value' => t('Clone layout'),
        '#submit' => array('ds_field_ui_layout_clone'),
        '#weight' => 21,
      );
    }
  }

  $form['ds_layouts']['old_layout'] = array(
    '#type' => 'value',
    '#value' => isset($layout['layout']) ? $layout['layout'] : 0,
  );

  // Add validate and submit handlers. Layout needs be first so
  // we can reset the type key for Field API fields.
  $form['#validate'][] = 'ds_field_ui_layouts_validate';
  array_unshift($form['actions']['submit']['#submit'], 'ds_field_ui_fields_save');
  array_unshift($form['actions']['submit']['#submit'], 'ds_field_ui_layouts_save');
}

/**
 * Add the layout previews to the Field UI screen.
 *
 * @param $form
 *   A collection of form properties.
 * @param FormStateInterface $form_state
 *   The state of the form
 * @param $ds_layouts
 *   Collection of all the layouts
 * @param $layout
 *   Current selected layout
 * @param $entity_type
 *   The name of the entity type.
 * @param $bundle
 *   The name of the bundle
 * @param $view_mode
 *   The name of the view_mode
 */
function _ds_field_ui_table_layouts_preview(&$form, FormStateInterface $form_state, $ds_layouts, $layout, $entity_type, $bundle, $view_mode) {
  $layout_string = '';

  $form['ds_layouts']['layout'] = array(
    '#type' => 'select',
    '#title' => t('Select a layout'),
    '#options' => $layout['layout_options'],
    '#default_value' => isset($layout['layout']) ? $layout['layout'] : '',
    '#weight' => -1,
    '#ajax' => array(
      'callback' => 'ds_field_ui_table_layouts_preview_callback',
      'wrapper' => 'ds_layout_wrapper',
    ),
  );

  if (!isset($layout['layout'])) {
    $form['ds_layouts']['layout']['#description'] = t("A layout must be selected to enable Display Suite functionality.");
  }

  $form['ds_layouts']['preview'] = array(
    '#type' => 'container',
    '#prefix' => '<div id="ds_layout_wrapper">',
    '#suffix' => '</div>',
    '#weight' => -3,
  );

  if (isset($layout['layout']) || $form_state->hasValue('layout')) {
    $layout_string = $form_state->hasValue('layout') ? $form_state->getValue('layout') : $layout['layout'];
  }

  if (!empty($layout_string)) {
    $chosen_layout = $ds_layouts[$layout_string];
    $selected = '<strong>' . $chosen_layout['label'] . '</strong>';
    $selected .= '<br/>' . t('The default template can be found in %path', array('%path' => $chosen_layout['template_path']));
    $suggestions_array = array();
    $suggestions_array[0] = $layout_string . '--' . $entity_type;
    $suggestions_array[2] = $layout_string . '--' . $entity_type . '-' . $bundle;
    $suggestions_array[4] = $layout_string . '--' . $entity_type . '--{id}';
    if ($view_mode != 'default') {
      $suggestions_array[1] = $layout_string . '--' . $entity_type . '-' . $view_mode;
      $suggestions_array[3] = $layout_string . '--' . $entity_type . '-' . $bundle . '-' . $view_mode;
    }

    ksort($suggestions_array);

    // Append correct extension.
    foreach ($suggestions_array as $key => $value) {
      $suggestion_replace = strtr($suggestions_array[$key], '_', '-');
      $suggestions_array[$key] = $suggestion_replace . '.html.twig';
    }

    if ($form_state->hasValue('layout') || (!empty($layout) && isset($layout['regions']))) {
      $fallback_image = drupal_get_path('module', 'ds') . '/images/preview.png';
      $current_layout = $form_state->hasValue('layout') && (!isset($layout->layout) || $form_state->getValue('layout') != $layout->layout) ? t('Current layout (after save)') : t('Current layout');
      $image = (isset($chosen_layout['icon']) && !empty($chosen_layout['icon'])) ? $chosen_layout['icon'] : $fallback_image;

      $form['ds_layouts']['preview']['title'] = array(
        '#markup' => '<div class="ds-layout-preview-title">' . $current_layout . '</div>',
      );
      $form['ds_layouts']['preview']['image'] = array(
        '#markup' => '<div class="ds-layout-preview-image"><img src="' . base_path() . $image . '"/></div>',
      );
      $form['ds_layouts']['preview']['info'] = array(
        '#type' => 'container',
        '#attributes' => array(
          'class' => array('ds-layout-preview-suggestion'),
        ),
      );
      $form['ds_layouts']['preview']['info']['suggestions'] = array(
        '#markup' => '<p>' . $selected . '</p><p>' . t('Template suggestions') . ':' . '</p>',
      );
      $form['ds_layouts']['preview']['info']['suggestions_list'] = array(
        '#theme' => 'item_list',
        '#items' => $suggestions_array,
      );

      if (!empty($chosen_layout['css'])) {
        $disable_css = FALSE;

        if (isset($layout['disable_css'])) {
          $disable_css = $layout['disable_css'];
        }
        if ($form_state->hasValue('disable_css') && $disable_css !== $form_state->getValue('disable_css')) {
          $disable_css = $form_state->getValue('disable_css');
        }

        $form['ds_layouts']['preview']['info']['settings']['disable_css'] = array(
          '#type' => 'checkbox',
          '#title' => t('Disable layout CSS styles'),
          '#default_value' => $disable_css,
        );
      }
      $form['ds_layouts']['preview']['clear'] = array(
        '#markup' => '<div class="ds-after-suggestion"></div>',
      );
    }

    if (!isset($layout['layout']) || $layout_string != $layout['layout']) {

      // Get admin path.
      $route = FieldUI::getOverviewRouteInfo($entity_type, $bundle);
      $route_parameters = $route->getRouteParameters();
      $route_parameters['view_mode_name'] = $view_mode;
      $options = $route->getOptions();
      $route_name = 'entity.entity_view_display.' . $entity_type . '.view_mode';

      $admin_path = \Drupal::service('url_generator')->generateFromRoute($route_name, $route_parameters, $options);
      $destination_url = '';

      // If regions aren't set we don't have to move fields.
      if (isset($layout['regions'])) {
        $route_name = 'ds.change_layout';
        $route_parameters = array(
          'entity_type' => $entity_type,
          'bundle' => $bundle,
          'display_mode' => $view_mode,
          'new_layout' => $layout_string,
        );
        $options = array();
        $destination_url = $admin_path;
      }

      $form['layout_changed_url'] = array(
        '#type' => 'value',
        '#value' => array(
          'route_name' => $route_name,
          'route_parameters' => $route_parameters,
          'destination_url' => $destination_url,
          'options' => $options,
        ),
      );

      array_unshift($form['actions']['submit']['#submit'], 'ds_field_ui_change_layout_submit');
    }
  }
}

/**
 * Ajax callback for _ds_field_ui_table_layouts_preview().
 */
function ds_field_ui_table_layouts_preview_callback($form, FormStateInterface $form_state) {
  return $form['ds_layouts']['preview'];
}

/**
 * Form submission handler for _ds_field_ui_table_layouts_preview().
 */
function ds_field_ui_change_layout_submit($form, FormStateInterface $form_state) {
  // Remove original destination
  \Drupal::request()->query->remove('destination');

  $destination = $form_state->getValue('layout_changed_url');

  $redirectUrl = new Url(
    $destination['route_name'],
    $destination['route_parameters'],
    $destination['options']
  );

  if (!empty($destination['destination_url'])) {
    $redirectUrl->setOption('query', array('destination' => $destination['destination_url']));
  }

  $form_state->setRedirectUrl($redirectUrl);
}

/**
 * Add the fields to the Field UI form.
 *
 * @param $entity_type
 *   The name of the entity type.
 * @param $bundle
 *   The name of the bundle
 * @param $view_mode
 *   The name of the view_mode
 * @param $form
 *   A collection of form properties.
 * @param FormStateInterface $form_state
 *   A collection of form_state properties.
 */
function _ds_field_ui_fields($entity_type, $bundle, $view_mode, &$form, FormStateInterface $form_state) {

  // Do not add the fields if there is no layout.
  if (!isset($form['#ds_layout'])) {
    return;
  }

  // Get the fields and put them on the form.
  $fields = Ds::getFields($entity_type);

  // Get field settings.
  $field_settings = $form['#ds_layout']['fields'];
  $form['#field_settings'] = $field_settings;

  $table = &$form['fields'];
  $form['#ds_fields'] = array();

  $field_label_options = array(
    'above' => t('Above'),
    'inline' => t('Inline'),
    'hidden' => t('- Hidden -'),
  );
  \Drupal::moduleHandler()->alter('ds_label_options', $field_label_options);

  /** @var EntityFormInterface $entity_form */
  $entity_form = $form_state->getFormObject();
  /** @var EntityViewDisplayInterface $display */
  $display = $entity_form->getEntity();
  $parent_options = array();

  if (function_exists('field_group_field_ui_form_params')) {
    $field_group_params = field_group_field_ui_form_params($form, $display);
    foreach ($field_group_params->groups as $name => $group) {
      $parent_options[$name] = $group->label;
    }
    $parent_options['_add_new_group'] = t('Add new group');
  }

  foreach ($fields as $key => $field) {
    $configuration = array(
      'field' => $field,
      'field_name' => $key,
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'view_mode' => $view_mode,
    );

    // Check if we can display this field here.
    /** @var $plugin_instance DsFieldInterface */
    $plugin_instance = \Drupal::service('plugin.manager.ds')->createInstance($field['plugin_id'], $configuration);

    if (!$plugin_instance->isAllowed()) {
      continue;
    }

    // Don't filter out fields when $displays is empty
    if (!empty($displays)) {
      $continue = TRUE;
      foreach ($displays as $limitation) {
        list($limit_bundle, $limit_view_mode) = explode('|', $limitation);
        if ($limit_bundle == '*' || $limit_bundle == $bundle) {
          if ($limit_view_mode == '*' || $limit_view_mode == $view_mode) {
            $continue = FALSE;
          }
        }
      }

      if ($continue) {
        continue;
      }
    }

    $form['#ds_fields'][] = $key;

    // Fetch saved plugin settings
    $form_state_plugin_settings = $form_state->get('plugin_settings');

    // Check on formatter settings.
    $plugin_settings = array();
    if (isset($form_state_plugin_settings[$key])) {
      $plugin_settings = $form_state_plugin_settings[$key];
    }
    elseif (isset($field_settings[$key]['settings']) || isset($field_settings[$key]['ft']) || isset($field_settings[$key]['ds_limit'])) {
      if (isset($field_settings[$key]['settings'])) {
        $plugin_settings = $field_settings[$key]['settings'];
      }
      if (isset($field_settings[$key]['ft'])) {
        $plugin_settings['ft'] = $field_settings[$key]['ft'];
      }
      if (isset($field_settings[$key]['ds_limit'])) {
        $plugin_settings['ds_limit'] = $field_settings[$key]['ds_limit'];
      }
      $form_state_plugin_settings[$key] = $plugin_settings;
    }
    $plugin_instance->setConfiguration($plugin_settings);

    // Save fetched plugin settings
    $form_state->set('plugin_settings', $form_state_plugin_settings);

    $hidden = array('hidden' => t('- Hidden -'));

    // Get the formatters from the field instance
    $formatters = $plugin_instance->formatters();

    // This should be temporary. Don't want to copy stuff from the object to
    // the field each ajax refresh.
    if (!empty($formatters)) {
      $formatters = $hidden + $formatters;
    }
    else {
      $formatters = $hidden + array('default' => t('Default'));
    }

    $table[$key] = array(
      '#row_type' => 'field',
      '#js_settings' => array('field'),
      '#region_callback' => 'field_ui_display_overview_row_region',
      '#attributes' => array('class' => array('draggable', 'tabledrag-leaf')),
      'human_name' => array(
        '#plain_text' => $field['title'],
      ),
      'weight' => array(
        '#type' => 'textfield',
        '#default_value' => isset($field_settings[$key]['weight']) ? $field_settings[$key]['weight'] : 0,
        '#size' => 3,
        '#attributes' => array('class' => array('field-weight')),
      ),
      'parent_wrapper' => array(
        'parent' => array(
          '#type' => 'select',
          '#empty_value' => '',
          '#options' => $parent_options,
          '#default_value' => isset($field_group_params->parents[$key]) ? $field_group_params->parents[$key] : '',
          '#attributes' => array('class' => array('field-parent')),
          '#parents' => array('fields', $key, 'parent'),
        ),
        'hidden_name' => array(
          '#type' => 'hidden',
          '#default_value' => $key,
          '#attributes' => array('class' => array('field-name')),
        ),
      ),
      'label' => array(
        '#type' => 'select',
        '#options' => $field_label_options,
        '#default_value' => isset($field_settings[$key]['label']) ? $field_settings[$key]['label'] : 'hidden',
      ),
      'plugin' => array(
        'type' => array(
          '#type' => 'select',
          '#options' => $formatters,
          '#default_value' => isset($field_settings[$key]['formatter']) ? $field_settings[$key]['formatter'] : 'hidden',
          '#attributes' => array('class' => array('field-plugin-type')),
        ),
      ),
      'settings_summary' => array(),
      'settings_edit' => array(),
    );

    if ($form_state->get('plugin_settings_edit') == $key) {
      $table[$key]['settings_summary']['#attributes']['colspan'] = 2;
      $settings_form = ds_field_settings_form($plugin_instance, $form_state);
      ds_field_row_form_format_construct($table, $key, $settings_form, $form_state);
    }
    else {
      // After saving, the settings are updated here as well. First we create
      // the element for the table cell.
      $summary = ds_field_settings_summary($plugin_instance, $plugin_settings, $form_state);

      if (!empty($summary)) {
        $table[$key]['settings_summary'] = $summary;
        ds_field_row_form_format_summary_construct($table, $key, $form_state);
      }
    }
  }
}

/**
 * Alter the core field on the the Field UI form.
 *
 * @param $form
 *   A collection of form properties.
 * @param FormStateInterface $form_state
 *   A collection of form_state properties.
 */
function _ds_field_ui_core_fields(&$form, FormStateInterface $form_state) {
  $entity_type = $form['#entity_type'];
  $bundle = $form['#bundle'];
  /** @var EntityFormInterface $entity_form */
  $entity_form = $form_state->getFormObject();
  /** @var EntityViewDisplay $entity_display */
  $entity_display = $entity_form->getEntity();

  // Gather type information.
  $instances = \Drupal::service('entity_field.manager')->getFieldDefinitions($entity_type, $bundle);
  $table = &$form['fields'];

  // Get all persisted values for fields and plugin settings
  $form_state_plugin_settings = $form_state->get('plugin_settings');

  // Field rows.
  foreach ($instances as $key => $instance) {
    if ($instance instanceof FieldConfigInterface) {

      $settings = $entity_display->getComponent($key);
      if (empty($settings)) {
        continue;
      }

      if (isset($form_state_plugin_settings[$key])) {
        $settings = array_merge($settings, $form_state_plugin_settings[$key]);
      }

      // Import field settings and merge with Field API settings.
      if (!isset($form_state_plugin_settings[$key])) {
        $form_state_plugin_settings[$key] = isset($settings['third_party_settings']['ds']) ? $settings['third_party_settings']['ds'] : array();
      }

      if ($form_state->get('plugin_settings_edit') == $key) {
        $table[$key]['plugin']['settings_edit_form']['actions']['save_settings']['#validate'] = array('ds_field_ui_fields_validate');
        $table[$key]['plugin']['settings_edit_form']['actions']['cancel_settings']['#validate'] = array('ds_field_ui_fields_validate');
      }
      else {
        $table[$key]['settings_edit'] = array();
        if (!empty($table[$key]['settings_summary'])) {
          ds_field_row_form_format_summary_construct($table, $key, $form_state);
        }
      }
    }
  }

  // Set updated plugin settings
  $form_state->set('plugin_settings', $form_state_plugin_settings);
}

/**
 * Helper function for building the formatter settings.
 */
function ds_field_row_form_format_construct(&$table, $key, $settings_form, FormStateInterface $form_state) {
  $build_info = $form_state->getBuildInfo();
  $base_button = array(
    '#submit' => array(
      array($build_info['callback_object'], 'multistepSubmit'),
    ),
    '#validate' => array(
      'ds_field_ui_fields_validate',
    ),
    '#ajax' => array(
      'callback' => array($build_info['callback_object'], 'multistepAjax'),
      'wrapper' => 'field-display-overview-wrapper',
      'effect' => 'fade',
    ),
    '#field_name' => $key,
  );
  $table[$key]['plugin']['settings_edit'] = array(
    '#type' => 'container',
    '#attributes' => array('class' => array('field-plugin-settings-edit-form')),
    '#parents' => array('fields', $key, 'settings_edit_form'),
    '#weight' => -5,
    // Create a settings form where hooks can pick in.
    'settings' => $settings_form,
    'actions' => array(
      '#type' => 'actions',
      'save_settings' => $base_button + array(
        '#type' => 'submit',
        '#name' => $key . '_plugin_settings_update',
        '#value' => t('Update'),
        '#op' => 'update',
      ),
      'cancel_settings' => $base_button + array(
        '#type' => 'submit',
        '#name' => $key . '_plugin_settings_cancel',
        '#value' => t('Cancel'),
        '#op' => 'cancel',
        // Do not check errors for the 'Cancel' button.
        '#limit_validation_errors' => array(),
      ),
    ),
  );
  $table[$key]['#attributes']['class'][] = 'field-plugin-settings-editing';
  $table[$key]['plugin']['type']['#attributes']['class'] = array('visually-hidden');
}

/**
 * Helper function for formatter summary settings.
 */
function ds_field_row_form_format_summary_construct(&$table, $key, FormStateInterface $form_state) {
  $build_info = $form_state->getBuildInfo();

  $base_button = array(
    '#submit' => array(array($build_info['callback_object'], 'multistepSubmit')),
    '#ajax' => array(
      'callback' => array($build_info['callback_object'], 'multistepAjax'),
      'wrapper' => 'field-display-overview-wrapper',
      'effect' => 'fade',
    ),
    '#field_name' => $key,
  );

  // Add the configure button.
  $table[$key]['settings_edit'] = $base_button + array(
    '#type' => 'image_button',
    '#name' => $key . '_plugin_settings_edit',
    '#src' => 'core/misc/icons/787878/cog.svg',
    '#attributes' => array('class' => array('field-plugin-settings-edit'), 'alt' => t('Edit')),
    '#op' => 'edit',
    // Do not check errors for the 'Edit' button, but make sure we get
    // the value of the 'plugin type' select.
    '#limit_validation_errors' => array(array('fields', $key, 'type')),
    '#prefix' => '<div class="field-plugin-settings-edit-wrapper">',
    '#suffix' => '</div>',
  );
}

/**
 * Utility function to sort a multidimensional array by a value in a sub-array.
 *
 * @param $a
 *   The array to sort.
 * @param $subkey
 *   The subkey to sort by.
 *
 * @return array
 *   The sorted array.
 */
function _ds_sort_fields($a, $subkey) {
  $c = array();
  foreach ($a as $k => $v) {
    if (isset($v[$subkey])) {
      $b[$k] = $v[$subkey];
    }
  }
  asort($b);
  foreach ($b as $key => $val) {
    $c[$key] = $a[$key];
  }
  return $c;
}
