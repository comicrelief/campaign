<?php

namespace Drupal\ds\Plugin\DsField;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Renders an entity by a given view mode.
 */
abstract class Entity extends DsFieldBase {

  /**
   * The EntityDisplayRepository service.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * Constructs a Display Suite field plugin.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, EntityDisplayRepositoryInterface $entity_display_repository) {
    $this->entityDisplayRepository = $entity_display_repository;

    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_display.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm($form, FormStateInterface $form_state) {
    $entity = $this->linkedEntity();
    $view_modes = $this->entityDisplayRepository->getViewModes($entity);

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
    $view_modes = $this->entityDisplayRepository->getViewModes($entity);

    // When no view modes are found no summary is displayed.
    if (empty($view_modes)) {
      return '';
    }

    // Print the chosen view mode or the default one.
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
    $view_modes = $this->entityDisplayRepository->getViewModes($entity);
    reset($view_modes);
    $default_view_mode = key($view_modes);

    $configuration = array(
      'entity_view_mode' => $default_view_mode,
    );

    return $configuration;
  }

  /**
   * Gets the wanted entity.
   */
  public function linkedEntity() {
    return '';
  }

  /**
   * Gets the view mode.
   */
  public function getEntityViewMode() {
    $config = $this->getConfiguration();
    return $config['entity_view_mode'];
  }

}
