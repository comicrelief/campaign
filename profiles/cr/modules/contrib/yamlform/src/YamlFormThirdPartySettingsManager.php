<?php

namespace Drupal\yamlform;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Form third party settings manager.
 */
class YamlFormThirdPartySettingsManager implements YamlFormThirdPartySettingsManagerInterface {

  use StringTranslationTrait;

  /**
   * The configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The module handler to load includes.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The path validator.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * The YAML Form module's default configuration settings.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Constructs a new YamlFormThirdPartySettingsManager.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration object factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler class to use for loading includes.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   The path validator.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler, PathValidatorInterface $path_validator) {
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
    $this->pathValidator = $path_validator;

    $this->config = $this->configFactory->getEditable('yamlform.settings');
    $this->loadIncludes();
  }

  /**
   * Load all third party settings includes.
   *
   * @see {module}/{module}.yamlform.inc
   * @see {module}/yamlform/{module}.yamlform.inc
   * @see yamlform/yamlform.{module}.inc
   */
  protected function loadIncludes() {
    $modules = array_keys($this->moduleHandler->getModuleList());
    foreach ($modules as $module) {
      $this->moduleHandler->loadInclude($module, 'yamlform.inc');
      $this->moduleHandler->loadInclude($module, 'yamlform.inc', "yamlform/$module");
      $this->moduleHandler->loadInclude('yamlform', "inc", "third_party_settings/yamlform.$module");
    }
  }

  /**
   * {@inheritdoc}
   */
  public function alter($type, &$data, &$context1 = NULL, &$context2 = NULL) {
    $this->moduleHandler->alter($type, $data, $context1, $context2);
  }

  /**
   * {@inheritdoc}
   */
  public function buildLinks() {
    return [
      '#theme' => 'links',
      '#links' => $this->getLinks(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getLinks() {
    $links = [];
    $links['honeypot'] = [
      'title' => $this->t('Honeypot'),
      'url' => $this->pathValidator->getUrlIfValid('https://www.drupal.org/project/honeypot'),
    ];
    return $links;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['third_party_settings'] = [
      '#tree' => TRUE,
    ];
    $form['#after_build'] = [[$this, 'afterBuild']];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function afterBuild(array $form, FormStateInterface $form_state) {
    // If third party settings are empty.
    if (!isset($form['third_party_settings']) || !Element::children($form['third_party_settings'])) {
      // Hide all actions including the 'Save configuration' button.
      $form['actions']['#access'] = FALSE;

      // Display a warning.
      drupal_set_message($this->t('There are no third party settings available. Please install a contributed module that integrates with the YAML Form module.'), 'warning');

      // Link to supported modules.
      $form['supported'] = [
        'title' => [
          '#markup' => $this->t('Supported modules.'),
          '#prefix' => '<h3>',
          '#suffix' => '</h3>',
        ],
        'description' => [
          '#markup' => $this->t('The below modules either provide form integration or the YAML Form module will automatically integrate with them when they are installed.'),
          '#prefix' => '<p>',
          '#suffix' => '</p>',
        ],
        'links' => $this->buildLinks(),
      ];
    }
    else {
      ksort($form['third_party_settings']);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function setThirdPartySetting($module, $key, $value) {
    $this->config->set("third_party_settings.$module.$key", $value);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getThirdPartySetting($module, $key, $default = NULL) {
    $value = $this->config->get("third_party_settings.$module.$key");
    return (isset($value)) ? $value : $default;
  }

  /**
   * {@inheritdoc}
   */
  public function getThirdPartySettings($module) {
    $this->config->get("third_party_settings.$module") ?: [];
  }

  /**
   * {@inheritdoc}
   */
  public function unsetThirdPartySetting($module, $key) {
    $this->config->clear("third_party_settings.$module.$key");
    // If the third party is no longer storing any information, completely
    // remove the array holding the settings for this module.
    if (!$this->config->get("third_party_settings.$module")) {
      $this->config->clear("third_party_settings.$module");
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getThirdPartyProviders() {
    $third_party_settings = $this->config->get('third_party_settings') ?: [];
    return array_keys($third_party_settings);
  }

}
