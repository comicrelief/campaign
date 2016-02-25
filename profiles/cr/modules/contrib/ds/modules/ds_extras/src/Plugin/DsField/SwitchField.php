<?php

/**
 * @file
 * Contains \Drupal\ds_extras\Plugin\DsField\SwitchField.
 */

namespace Drupal\ds_extras\Plugin\DsField;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\ds\Plugin\DsField\DsFieldBase;

/**
 * Plugin that generates a link to switch view mode with via ajax.
 *
 * @DsField(
 *   id = "switch_field",
 *   title = @Translation("Switch field"),
 *   entity_type = "node"
 * )
 */
class SwitchField extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $settings = $this->getConfiguration();

    if (!empty($settings)) {
      /** @var EntityInterface $entity */
      $entity = $this->entity();

      // Basic route parameters
      $route_parameters = array(
        'entityType' => $entity->getEntityTypeId(),
        'entityId' => $entity->id(),
      );

      $selector = $this->viewMode() == 'default' ? 'full' : $this->viewMode();
      // Basic route options
      $route_options = array(
        'query' => array(
          'selector' => 'view-mode-' . $selector,
        ),
        'attributes' => array(
          'class' => array(
            'use-ajax',
          ),
        ),
      );

      foreach ($settings['vms'] as $key => $value) {
        // If the label is empty, do not create a link
        if (!empty($value)) {
          $route_parameters['viewMode'] = $key == 'default' ? 'full' : $key;
          $items[] = \Drupal::l($value, Url::fromRoute('ds_extras.switch_view_mode', $route_parameters, $route_options));
        }
      }
    }

    $output = array();
    if (!empty($items)) {
      $output = array(
        '#theme' => 'item_list',
        '#items' => $items,
        // Add the AJAX library to the field for inline switching support.
        '#attached' => array(
          'library' => array(
            'core/drupal.ajax',
          ),
        ),
      );
    }

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm($form, FormStateInterface $form_state) {
    $entity_type = $this->getEntityTypeId();
    $bundle = $this->bundle();
    $view_modes = \Drupal::service('entity_display.repository')->getViewModes($entity_type);

    $form['info'] = array(
      '#markup' => t('Enter a label for the link for the view modes you want to switch to.<br />Leave empty to hide link. They will be localized.'),
    );

    $config = $this->getConfiguration();
    $config = isset($config['vms']) ? $config['vms'] : array();
    foreach ($view_modes as $key => $value) {
      $entity_display = entity_load('entity_view_display', $entity_type .  '.' . $bundle . '.' . $key);
      if (!empty($entity_display)) {
        if ($entity_display->status()) {
          $form['vms'][$key] = array(
            '#type' => 'textfield',
            '#default_value' => isset($config[$key]) ? $config[$key] : '',
            '#size' => 20,
            '#title' => Html::escape($value['label']),
          );
        }
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary($settings) {
    $entity_type = $this->getEntityTypeId();
    $bundle = $this->bundle();
    $settings = isset($settings['vms']) ? $settings['vms'] : array();
    $view_modes = \Drupal::service('entity_display.repository')->getViewModes($entity_type);

    $summary[] = 'View mode labels';

    foreach ($view_modes as $key => $value) {
      $entity_display = entity_load('entity_view_display', $entity_type .  '.' . $bundle . '.' . $key);
      if (!empty($entity_display)) {
        if ($entity_display->status()) {
          $label = isset($settings[$key]) ? $settings[$key] : $key;
          $summary[] = $key . ' : ' . $label;
        }
      }
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function isAllowed() {
    if (\Drupal::config('ds_extras.settings')->get('switch_field')) {
      return TRUE;
    }

    return FALSE;
  }

}
