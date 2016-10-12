<?php

/**
 * @file
 * Integrates third party settings for the YAML Form module.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_FORM_ID_alter().
 */
function yamlform_test_third_party_settings_form_yamlform_admin_third_party_settings_form_alter(&$form, FormStateInterface $form_state) {
  /** @var \Drupal\yamlform\YamlFormThirdPartySettingsManagerInterface $third_party_settings_manager */
  $third_party_settings_manager = \Drupal::service('yamlform.third_party_settings_manager');

  $form['third_party_settings']['yamlform_test_third_party_settings'] = [
    '#type' => 'details',
    '#title' => t('Form test third party admin settings'),
    '#open' => TRUE,
  ];
  $form['third_party_settings']['yamlform_test_third_party_settings']['message'] = [
    '#type' => 'textfield',
    '#title' => t('Display the below message on every form'),
    '#default_value' => $third_party_settings_manager->getThirdPartySetting('yamlform_test_third_party_settings', 'message'),
  ];
}

/**
 * Implements hook_form_FORM_ID_alter().
 */
function yamlform_test_third_party_settings_form_yamlform_third_party_settings_form_alter(&$form, FormStateInterface $form_state) {
  /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
  $yamlform = $form_state->getFormObject()->getEntity();

  $form['third_party_settings']['yamlform_test_third_party_settings'] = [
    '#type' => 'details',
    '#title' => t('Form test third party settings'),
    '#open' => TRUE,
  ];
  $form['third_party_settings']['yamlform_test_third_party_settings']['message'] = [
    '#type' => 'textfield',
    '#title' => t('Display the below message'),
    '#default_value' => $yamlform->getThirdPartySetting('yamlform_test_third_party_settings', 'message'),
  ];
}

/**
 * Implements hook_yamlform_submission_form_alter().
 */
function yamlform_test_third_party_settings_yamlform_submission_form_alter(&$form, FormStateInterface $form_state, $form_id) {
  if (!empty($_POST)) {
    return;
  }

  /** @var \Drupal\yamlform\YamlFormThirdPartySettingsManagerInterface $third_party_settings_manager */
  $third_party_settings_manager = \Drupal::service('yamlform.third_party_settings_manager');

  /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
  $yamlform_submission = $form_state->getFormObject()->getEntity();
  $yamlform = $yamlform_submission->getYamlForm();

  // Get message from the form settings or the form admin settings.
  $message = $yamlform->getThirdPartySetting('yamlform_test_third_party_settings', 'message') ?:
    $third_party_settings_manager->getThirdPartySetting('yamlform_test_third_party_settings', 'message');

  // If a message is set, display it.
  if ($message) {
    drupal_set_message($message);
  }
}
