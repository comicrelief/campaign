<?php

/**
 * @file
 * Contains \Drupal\ds\Form\FieldFormBase.
 */

namespace Drupal\ds\Form;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base form for fields.
 */
class FieldFormBase extends ConfigFormBase implements ContainerInjectionInterface {

  /**
   * Holds the entity type manager
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Holds the cache invalidator
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheInvalidator;

  /**
   * Drupal module handler
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * The field properties.
   *
   * @var array
   */
  protected $field;

  /**
   * Constructs a \Drupal\system\CustomFieldFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   *   The cache invalidator.
   * @param \Drupal\Core\Extension\ModuleHandler
   *   The module handler.
   */
  public function __construct(ConfigFactory $config_factory, EntityTypeManagerInterface $entity_type_manager, CacheTagsInvalidatorInterface $cache_invalidator, ModuleHandler $module_handler) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entity_type_manager;
    $this->cacheInvalidator = $cache_invalidator;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('cache_tags.invalidator'),
      $container->get('module_handler')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ds_custom_field_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $field_key = '') {
    // Initialize field.
    $field = array();

    // Fetch field if it already exists.
    if (!empty($field_key)) {
      $field = $this->config('ds.field.' . $field_key)->get();
    }

    // Save the field for future reuse.
    $this->field = $field;

    $form['name'] = array(
      '#title' => t('Label'),
      '#type' => 'textfield',
      '#default_value' => isset($field['label']) ? $field['label'] : '',
      '#description' => t('The human-readable label of the field.'),
      '#maxlength' => 128,
      '#required' => TRUE,
      '#size' => 30,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => isset($field['id']) ? $field['id'] : '',
      '#maxlength' => 32,
      '#description' => t('The machine-readable name of this field. This name must contain only lowercase letters and underscores. This name must be unique.'),
      '#disabled' => !empty($field['id']),
      '#machine_name' => array(
        'exists' => array($this, 'uniqueFieldName'),
        'source' => array('name'),
      ),
    );

    $entity_options = array();
    $entities = $this->entityTypeManager->getDefinitions();
    foreach ($entities as $entity_type => $entity_info) {
      if ($entity_info->get('field_ui_base_route') || $entity_type == 'ds_views') {
        $entity_options[$entity_type] = Unicode::ucfirst(str_replace('_', ' ', $entity_type));
      }
    }
    $form['entities'] = array(
      '#title' => t('Entities'),
      '#description' => t('Select the entities for which this field will be made available.'),
      '#type' => 'checkboxes',
      '#required' => TRUE,
      '#options' => $entity_options,
      '#default_value' => isset($field['entities']) ? $field['entities'] : array(),
    );

    $form['ui_limit'] = array(
      '#title' => t('Limit field'),
      '#description' => t('Limit this field on field UI per bundles and/or view modes. The values are in the form of $bundle|$view_mode, where $view_mode may be either a view mode set to use custom settings, or \'default\'. You may use * to select all, e.g article|*, *|full or *|*. Enter one value per line.'),      '#type' => 'textarea',
      '#default_value' => isset($field['ui_limit']) ? $field['ui_limit'] : '',
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save'),
      '#weight' => 100,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $field = array();
    $field['id'] = $form_state->getValue('id');
    $field['label'] = $form_state->getValue('name');
    $field['ui_limit'] = $form_state->getValue('ui_limit');
    $field['properties'] = $this->getProperties($form_state);
    $field['type'] = $this->getType();
    $field['type_label'] = $this->getTypeLabel();

    $entities = $form_state->getValue('entities');
    foreach ($entities as $key => $value) {
      if ($key !== $value) {
        unset($entities[$key]);
      }
    }
    $field['entities'] = $entities;

    // Save field to property
    $this->field = $field;

    // Save field values
    $this->config('ds.field.' . $field['id'])->setData($field)->save();

    // Clear caches and redirect
    $this->finishSubmitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    if (isset($this->field, $this->field['id'])) {
      return array(
        'ds.field.' . $this->field['id'],
      );
    }
    else {
      return array();
    }
  }

  /**
   * Returns the properties for the custom field
   */
  public function getProperties(FormStateInterface $form_state) {
    return array();
  }

  /**
   * Returns the type of the field.
   */
  public function getType() {
    return '';
  }

  /**
   * Returns the admin label for the field on the field overview page
   */
  public function getTypeLabel() {
    return '';
  }


  /**
   * Returns whether a field machine name is unique.
   */
  public function uniqueFieldName($name) {
    $value = strtr($name, array('-' => '_'));
    $config = $this->configFactory()->get('ds.field.' . $value);
    if ($config->get()) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Finishes the submit
   */
  public function finishSubmitForm(array &$form, FormStateInterface $form_state) {
    $field = $this->field;

    // Save field and clear ds_fields_info cache.
    $this->cacheInvalidator->invalidateTags(array('ds_fields_info'));

    // Also clear the ds plugin cache
    \Drupal::service('plugin.manager.ds')->clearCachedDefinitions();

    // Redirect.
    $url = new Url('ds.fields_list');
    $form_state->setRedirectUrl($url);
    drupal_set_message(t('The field %field has been saved.', array('%field' => $field['label'])));
  }

}
