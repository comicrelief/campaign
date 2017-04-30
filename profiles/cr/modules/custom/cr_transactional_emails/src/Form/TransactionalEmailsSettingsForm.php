<?php

/**
 * @file
 * Contains \Drupal\cr_transactional_emails\Form\TransactionalEmailsSettingsForm.
 */

namespace Drupal\cr_transactional_emails\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Xss;

/**
 * Configure module settings for this site.
 */
class TransactionalEmailsSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cr_transactional_emails_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return array(
      'cr_transactional_emails.settings',
    );
  }

  /**
   * Returns list of email delivery providers.
   *
   * @return array
   *   Associated array of email delivery providers:
   *   Construct: 'machine_name' => 'provider name label'
   */
  protected function getServiceProviders() {
    // NOTES:
    // The following returned list just contains SmartFocus.
    // Ideally would be good to have code which scans the plugins available.
    // From the plugins avaliable, we can then get the list from those class
    // methods. E.g. `getId()` and `getName()` to build the return array.
    return array('smartfocus' => 'SmartFocus');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get config.
    $config = $this->config('cr_transactional_emails.settings');

    // Get list of service providers.
    $service_providers = $this->getServiceProviders();

    // Build form.
    $form = array();

    $form['service_provider'] = array(
      '#type' => 'fieldset',
      '#title' => t('Email delivery platforms'),
      '#attributes' => array('class' => array('trans-email-admin-settings')),
    );

    $form['service_provider']['selected_api'] = array(
      '#type' => 'radios',
      '#title' => t('Select service provider'),
      '#options' => $service_providers,
      '#default_value' => $config->get('selected_api') ?: '',
    );

    foreach ($service_providers as $provider_code => $provider_label) {
      $form[$provider_code] = array(
        '#type' => 'fieldset',
        '#title' => t('@provider_label settings', array('@provider_label' => $provider_label)),
        '#attributes' => array('class' => array('trans-email-admin-settings')),
      );

      $endpoint = $config->get($provider_code . '_api_endpoint') ?: '';

      $form[$provider_code][$provider_code . '_api_endpoint'] = array(
        '#type' => 'url',
        '#title' => t('API Endpoint'),
        '#description' => t('URL address to API Service'),
        '#default_value' => Xss::filterAdmin($endpoint),
      );
    }

    // Return form structure after passing it through its parent class method.
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get list of service providers.
    $provider_codes = array_keys($this->getServiceProviders());

    // Grab and save configuration settings.
    $config = $this->config('cr_transactional_emails.settings');
    $config->set('selected_api', $form_state->getValue('selected_api'));
    foreach ($provider_codes as $provider_code) {
      $setting_name = $provider_code . '_api_endpoint';
      $config->set($setting_name, $form_state->getValue($setting_name));
    }
    $config->save();

    // Pass form state through its parent class method.
    parent::submitForm($form, $form_state);
  }

}
