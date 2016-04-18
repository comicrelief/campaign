<?php

/**
 * @file
 * Contains \Drupal\diff\FieldDiffBuilderBase
 */

namespace Drupal\diff;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityManagerInterface;

abstract class FieldDiffBuilderBase extends PluginBase implements FieldDiffBuilderInterface, ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Contains the configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The entity parser.
   *
   * @var \Drupal\diff\DiffEntityParser
   */
  protected $entityParser;

  /**
   * Constructs a FieldDiffBuilderBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The configuration factory object.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   *   The entity manager.
   * @param \Drupal\diff\DiffEntityParser $entityParser
   *   The entity manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config, EntityManagerInterface $entityManager, DiffEntityParser $entityParser) {
    $this->configFactory = $config;
    $this->entityManager = $entityManager;
    $this->entityParser = $entityParser;
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->configuration += $this->defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('entity.manager'),
      $container->get('diff.entity_parser')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['show_header'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Show field title'),
      '#weight' => -5,
      '#default_value' => $this->configuration['show_header'],
    );
    $form['markdown'] = array(
      '#type' => 'select',
      '#title' => $this->t('Markdown callback'),
      '#default_value' => $this->configuration['markdown'],
      '#options' => array(
        'drupal_html_to_text' => $this->t('Drupal HTML to Text'),
        'filter_xss' => $this->t('Filter XSS (some tags)'),
        'filter_xss_all' => $this->t('Filter XSS (all tags)'),
      ),
      '#description' => $this->t('These provide ways to clean markup tags to make comparisons easier to read.'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // By default an empty validation function is provided.
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['show_header'] = $form_state->getValue('show_header');
    $this->configuration['markdown'] = $form_state->getValue('markdown');
    $this->configuration['#field_type'] = $form_state->get('field_type');
    $this->setConfiguration($this->configuration);
    $this->getConfiguration()->save();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'show_header' => 1,
      'markdown' => 'drupal_html_to_text',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configFactory->getEditable('diff.plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $config = $this->configFactory->getEditable('diff.plugins');
    $field_type = $configuration['#field_type'];
    unset($configuration['#field_type']);

    $field_type_settings = array();
    foreach ($configuration as $key => $value) {
      $field_type_settings[$key] = $value;
    }
    $settings = array(
      'type' => $this->pluginId,
      'settings' => $field_type_settings,
    );
    $config->set('field_types.' . $field_type, $settings);
    $config->save();
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return array();
  }

}
