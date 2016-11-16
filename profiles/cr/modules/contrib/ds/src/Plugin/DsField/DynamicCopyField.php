<?php

namespace Drupal\ds\Plugin\DsField;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ds\Plugin\DsPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a generic dynamic field that holds a copy of an existing ds field.
 *
 * @DsField(
 *   id = "dynamic_copy_field",
 *   deriver = "Drupal\ds\Plugin\Derivative\DynamicCopyField",
 * )
 */
class DynamicCopyField extends DsFieldBase {

  /**
   * The loaded instance.
   *
   * @var \Drupal\ds\Plugin\DsField\DsFieldInterface;
   */
  private $fieldInstance;

  /**
   * Constructs a Display Suite field plugin.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, DsPluginManager $plugin_Manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->fieldInstance = $plugin_Manager->createInstance($plugin_definition['properties']['ds_plugin'], $configuration);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.ds')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return $this->fieldInstance->build();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm($form, FormStateInterface $form_state) {
    return $this->fieldInstance->settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary($settings) {
    return $this->fieldInstance->settingsSummary($settings);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->fieldInstance->getConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    return $this->fieldInstance->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function formatters() {
    return $this->fieldInstance->formatters();
  }

}
