<?php

/**
 * @file
 * Contains \Drupal\ds\Form\ChangeLayoutForm.
 */

namespace Drupal\ds\Form;

use Drupal\Core\Entity\Display\EntityDisplayInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ds\Ds;

/**
 * Provides a configuration form for configurable actions.
 */
class ChangeLayoutForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ds_change_layout';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $entity_type = '', $bundle = '', $display_mode = '', $new_layout = '') {

    $old_layout = NULL;
    $all_layouts = Ds::getLayouts();

    if (!empty($entity_type) && !empty($bundle) && !empty($display_mode)) {
      $display = entity_get_display($entity_type, $bundle, $display_mode);
      $old_layout = $display->getThirdPartySettings('ds');
    }

    if ($old_layout && isset($all_layouts[$new_layout])) {

      $new_layout_key = $new_layout;
      $new_layout = $all_layouts[$new_layout];
      $old_layout_info = $all_layouts[$old_layout['layout']['id']];

      $form['#entity_type'] = $entity_type;
      $form['#entity_bundle'] = $bundle;
      $form['#mode'] = $display_mode;
      $form['#old_layout'] = $old_layout;
      $form['#old_layout_info'] = $old_layout_info;
      $form['#new_layout'] = $new_layout;
      $form['#new_layout_key'] = $new_layout_key;

      $form['info'] = array(
        '#markup' => t('You are changing from @old to @new layout for @bundle in @view_mode view mode.', array('@old' => $old_layout_info['label'], '@new' => $new_layout['label'], '@bundle' => $bundle, '@view_mode' => $display_mode)),
        '#prefix' => "<div class='change-ds-layout-info'>",
        '#suffix' => "</div>",
      );

      // Old region options.
      $regions = array();
      foreach ($old_layout_info['regions'] as $key => $info) {
        $regions[$key] = $info['label'];
      }

      // Let other modules alter the regions.
      // For old regions.
      $context = array(
        'entity_type' => $entity_type,
        'bundle' => $bundle,
        'view_mode' => $display_mode,
      );
      $region_info = array(
        'region_options' => $regions,
      );
      \Drupal::moduleHandler()->alter('ds_layout_region', $context, $region_info);
      $regions = $region_info['region_options'];
      $form['#old_layout_info']['layout']['regions'] = $regions;

      // For new regions.
      $new_regions = array();
      foreach ($new_layout['regions'] as $key => $info) {
        $new_regions[$key] = $info['label'];
      }
      $region_info = array(
        'region_options' => $new_regions,
      );
      \Drupal::moduleHandler()->alter('ds_layout_region', $context, $region_info);
      $new_layout['regions'] = $region_info['region_options'];
      $form['#new_layout']['regions'] = $new_layout['regions'];

      // Display the region options
      $selectable_regions = array('' => t('- None -')) + $new_layout['regions'];
      $form['regions_pre']['#markup'] = '<div class="ds-layout-regions">';
      foreach ($regions as $region => $region_title) {
        $form['region_' . $region] = array(
          '#type' => 'container',
        );
        $form['region_' . $region]['ds_label_' . $region] = array(
          '#markup' => 'Fields in <span class="change-ds-layout-old-region"> ' . $region_title . '</span> go into',
        );
        $form['region_' . $region]['ds_' . $region] = array(
          '#type' => 'select',
          '#options' => $layout_options = $selectable_regions,
          '#default_value' => $region,
        );
      }
      $form['regions_post']['#markup'] = '</div>';

      // Show previews from old and new layouts
      $form['preview'] = array(
        '#type' => 'container',
        '#prefix' => '<div class="ds-layout-preview">',
        '#suffix' => '</div>',
      );

      $fallback_image = drupal_get_path('module', 'ds') . '/images/preview.png';
      $old_image = (isset($old_layout_info['icon']) && !empty($old_layout_info['icon'])) ? $old_layout_info['icon'] : $fallback_image;
      $new_image = (isset($new_layout['icon']) &&  !empty($new_layout['icon'])) ? $new_layout['icon'] : $fallback_image;
      $arrow = drupal_get_path('module', 'ds') . '/images/arrow.png';

      $form['preview']['old_layout'] = array(
        '#markup' => '<div class="ds-layout-preview-image"><img src="' . base_path() . $old_image . '"/></div>',
      );
      $form['preview']['arrow'] = array(
        '#markup' => '<div class="ds-layout-preview-arrow"><img src="' . base_path() . $arrow . '"/></div>',
      );
      $form['preview']['new_layout'] = array(
        '#markup' => '<div class="ds-layout-preview-image"><img src="' . base_path() . $new_image . '"/></div>',
      );
      $form['#attached']['library'][] = 'ds/admin';

      // Submit button
      $form['actions'] = array('#type' => 'actions');
      $form['actions']['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Save'),
        '#prefix' => '<div class="ds-layout-change-save">',
        '#suffix' => '</div>',
      );
    }
    else {
      $form['nothing'] = array('#markup' => t('No valid configuration found.'));
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Prepare some variables.
    $old_layout = $form['#old_layout'];
    $new_layout = $form['#new_layout'];
    $old_layout_info = $form['#old_layout_info'];
    $new_layout_key = $form['#new_layout_key'];
    $entity_type = $form['#entity_type'];
    $bundle = $form['#entity_bundle'];
    $display_mode = $form['#mode'];

    // Create new third party settings
    $third_party_settings = $old_layout;
    $third_party_settings['layout']['id'] = $new_layout_key;
    if (!empty($new_layout['library'])) {
      $third_party_settings['layout']['library'] = $new_layout['library'];
    }
    $third_party_settings['layout']['path'] = $new_layout['path'];
    unset($third_party_settings['regions']);

    // map old regions to new ones
    foreach ($old_layout_info['layout']['regions'] as $region => $region_title) {
      $new_region = $form_state->getValue('ds_' . $region);
      if ($new_region != '' && isset($old_layout['regions'][$region])) {
        foreach ($old_layout['regions'][$region] as $field) {
          if (!isset($third_party_settings['regions'][$new_region])) {
            $third_party_settings['regions'][$new_region] = array();
          }
          $third_party_settings['regions'][$new_region][] = $field;
        }
      }
    }

    // Save configuration.
    /** @var $entity_display EntityDisplayInterface*/
    $entity_display = entity_load('entity_view_display', $entity_type . '.' . $bundle . '.' . $display_mode);
    foreach (array_keys($third_party_settings) as $key) {
      $entity_display->setThirdPartySetting('ds', $key, $third_party_settings[$key]);
    }
    $entity_display->save();

    // Clear entity info cache.
    \Drupal::service('entity_field.manager')->clearCachedFieldDefinitions();

    // Show message.
    drupal_set_message(t('The layout change has been saved.'));
  }

}
