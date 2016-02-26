<?php

/**
 * @file
 * Contains \Drupal\ds\Plugin\DsField\Entity.
 */

namespace Drupal\ds\Plugin\DsField;
use Drupal\Core\Form\FormStateInterface;

/**
 * Renders an entity by a given view mode.
 */
abstract class Entity extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm($form, FormStateInterface $form_state) {
    $entity = $this->linkedEntity();
    $view_modes = \Drupal::service('entity_display.repository')->getViewModes($entity);

    $options = array();
    foreach ($view_modes as $id => $view_mode) {
      $options[$id] = $view_mode['label'];
    }

    $config = $this->getConfiguration();
    $form['entity_view_mode'] = array(
      '#type' => 'select',
      '#title' => 'View mode',
      '#default_value' => $config['entity_view_mode'],
      '#options' => $options,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary($settings) {
    $entity = $this->linkedEntity();
    $view_modes = \Drupal::service('entity_display.repository')->getViewModes($entity);

    // When no view modes are found no summary is displayed
    if (empty($view_modes)) {
      return '';
    }

    // Print the chosen view mode or the default one
    $config = $this->getConfiguration();
    $entity_view_mode = $config['entity_view_mode'];
    $summary[] = 'View mode: ' . $view_modes[$entity_view_mode]['label'];

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $entity = $this->linkedEntity();
    $view_modes = \Drupal::service('entity_display.repository')->getViewModes($entity);
    reset($view_modes);
    $default_view_mode = key($view_modes);

    $configuration = array(
      'entity_view_mode' => $default_view_mode,
    );

    return $configuration;
  }

  /**
   * Gets the wanted entity
   */
  public function linkedEntity() {
    return '';
  }

  /**
   * Gets the view mode
   */
  public function getEntityViewMode() {
    $config = $this->getConfiguration();
    return $config['entity_view_mode'];
  }

}

