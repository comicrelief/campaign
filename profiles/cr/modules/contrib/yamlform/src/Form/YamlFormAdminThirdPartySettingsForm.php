<?php

namespace Drupal\yamlform\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormThirdPartySettingsManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure form third party settings for this site.
 */
class YamlFormAdminThirdPartySettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'yamlform_admin_third_party_settings_form';
  }

  /**
   * The form third party settings manager.
   *
   * @var \Drupal\yamlform\YamlFormThirdPartySettingsManagerInterface
   */
  protected $thirdPartySettingsManager;

  /**
   * Constructs a YamlFormAdminThirdPartySettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\yamlform\YamlFormThirdPartySettingsManagerInterface $third_party_settings_manager
   *   The form third party settings manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, YamlFormThirdPartySettingsManagerInterface $third_party_settings_manager) {
    parent::__construct($config_factory);
    $this->thirdPartySettingsManager = $third_party_settings_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('yamlform.third_party_settings_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['yamlform.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = $this->thirdPartySettingsManager->buildForm($form, $form_state);
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('yamlform.settings');
    $third_party_settings = $form_state->getValue('third_party_settings') + ($config->get('third_party_settings') ?: []);
    $config->set('third_party_settings', $third_party_settings);
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
