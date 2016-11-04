<?php

namespace Drupal\ds\Form;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
use Drupal\Core\Theme\Registry;
use Drupal\Core\Url;
use Drupal\ds\Ds;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configures Display Suite settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The theme registry used.
   *
   * @var \Drupal\Core\Theme\Registry
   */
  protected $themeRegistry;

  /**
   * The route builder.
   *
   * @var \Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routeBuilder;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Constructs a \Drupal\ds\Form\SettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Theme\Registry $theme_registry
   *   The theme registry used.
   * @param \Drupal\Core\Routing\RouteBuilderInterface $route_builder
   *   The route builder.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   */
  public function __construct(ConfigFactory $config_factory, ModuleHandlerInterface $module_handler, Registry $theme_registry, RouteBuilderInterface $route_builder, EntityFieldManagerInterface $entity_field_manager) {
    parent::__construct($config_factory);

    $this->moduleHandler = $module_handler;
    $this->themeRegistry = $theme_registry;
    $this->routeBuilder = $route_builder;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('theme.registry'),
      $container->get('router.builder'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ds_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ds.settings');

    $form['additional_settings'] = array(
      '#type' => 'vertical_tabs',
      '#attached' => array(
        'library' => array('ds/admin'),
      ),
    );

    $form['fs1'] = array(
      '#type' => 'details',
      '#title' => $this->t('Field Templates'),
      '#group' => 'additional_settings',
      '#weight' => 1,
      '#tree' => TRUE,
      '#collapsed' => FALSE,
    );

    $form['fs1']['field_template'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Field Templates'),
      '#description' => $this->t('Customize the labels and the HTML output of your fields.'),
      '#default_value' => $config->get('field_template'),
    );

    $theme_functions = Ds::getFieldLayoutOptions();
    $url = new Url('ds.classes');
    $description = $this->t('<br/>Default will output the field as defined in Drupal Core.<br/>' .
      'Reset will strip all HTML.<br/>' .
      'Minimal adds a simple wrapper around the field.<br/>' .
      'There is also an Expert Field Template that gives full control over the HTML, but can only be set per field.<br/><br/>' .
      'You can override this setting per field on the "Manage display" screens or when creating fields on the instance level.<br/><br/>' .
      '<strong>Template suggestions</strong><br/>' .
      'You can create .html.twig files as well for these field theme functions, e.g. field--reset.html.twig, field--minimal.html.twig<br/><br/>' .
      '<label>CSS classes</label>You can add custom CSS classes on the <a href=":url">classes form</a>. These classes can be added to fields using the Default Field Template.<br/><br/>' .
      '<label>Advanced</label>You can create your own custom field templates plugin. See Drupal\ds_test\Plugin\DsFieldTemplate for an example.', array(':url' => $url->toString()));

    $form['fs1']['ft-default'] = array(
      '#type' => 'select',
      '#title' =>$this->t('Default Field Template'),
      '#options' => $theme_functions,
      '#default_value' => $config->get('ft-default'),
      '#description' => $description,
      '#states' => array(
        'visible' => array(
          'input[name="fs1[field_template]"]' => array('checked' => TRUE),
        ),
      ),
    );

    $form['fs1']['ft-show-colon'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Show colon'),
      '#default_value' => $config->get('ft-show-colon'),
      '#description' => $this->t('Show the colon on the reset field template.'),
      '#states' => array(
        'visible' => array(
          'select[name="fs1[ft-default]"]' => array('value' => 'reset'),
          'input[name="fs1[field_template]"]' => array('checked' => TRUE),
        ),
      ),
    );

    $form['fs3'] = array(
      '#type' => 'details',
      '#title' => $this->t('Other'),
      '#group' => 'additional_settings',
      '#weight' => 3,
      '#tree' => TRUE,
    );
    $form['fs3']['use_field_names'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Use field names in templates'),
      '#default_value' => $config->get('use_field_names'),
      '#description' => $this->t('Use field names in twig templates instead of the key'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $values = $form_state->getValues();
    $this->config('ds.settings')
      ->set('field_template', $values['fs1']['field_template'])
      ->set('ft-default', $values['fs1']['ft-default'])
      ->set('ft-show-colon', $values['fs1']['ft-show-colon'])
      ->set('use_field_names', $values['fs3']['use_field_names'])
      ->save();

    $this->entityFieldManager->clearCachedFieldDefinitions();
    $this->moduleHandler->resetImplementations();
    $this->themeRegistry->reset();
    $this->routeBuilder->setRebuildNeeded();

    \Drupal::cache('render')->deleteAll();
    if ($this->moduleHandler->moduleExists('dynamic_page_cache')) {
      \Drupal::cache('dynamic_page_cache')->deleteAll();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return array(
      'ds.settings',
    );
  }

}
