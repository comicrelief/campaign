<?php

/**
 * @file
 * Integrates third party settings on the Honeypot module's behalf.
 */

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Flag to indicate that a honeypot setting can be set.
 */
define('YAMLFORM_HONEYPOT_NEUTRAL', -1);

/**
 * Flag to indicate that a honeypot setting is disabled for all forms.
 */
define('YAMLFORM_HONEYPOT_DISABLED_ALL', 0);

/**
 * Flag to indicate that a honeypot setting is enabled for all forms.
 */
define('YAMLFORM_HONEYPOT_ENABLED_ALL', 1);

/**
 * Flag to indicate that a honeypot setting is disabled for all forms.
 */
define('YAMLFORM_HONEYPOT_DISABLED_YAMLFORM', 2);

/**
 * Flag to indicate that a honeypot setting is enabled for all forms.
 */
define('YAMLFORM_HONEYPOT_ENABLED_YAMLFORM', 3);

/**
 * Alter form third party settings forms to include Honeypot configuration.
 *
 * @param array $form
 *   An associative array containing the structure of the form.
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The current state of the form.
 * @param bool $honeypot
 *   TRUE if honeypot protection is enabled.
 * @param int $honeypot_state
 *   Flag that determines if honeypot protection is enabled, disabled, or can be
 *   set.
 * @param bool $time_restriction
 *   TRUE if honeypot time restriction is enabled.
 * @param int $time_restriction_state
 *   Flag that determines if honeypot time restriction is enabled, disabled,
 *   or can be set.
 * @param string|\Drupal\Core\StringTranslation\TranslatableMarkup $label
 *   The label to displayed within the checkbox titles.
 */
function _yamlform_honeypot_form(&$form, FormStateInterface $form_state, $honeypot, $honeypot_state, $time_restriction, $time_restriction_state, $label) {
  $t_args = [
    '%label' => $label,
    ':href_honeypot' => Url::fromRoute('honeypot.config')->toString(),
    ':href_yamlform' => Url::fromRoute('yamlform.settings')->toString(),
  ];

  // Honeypot.
  $form['third_party_settings']['honeypot'] = [
    '#type' => 'details',
    '#title' => t('Honeypot'),
    '#open' => TRUE,
    '#description' => t('Mitigate SPAM form submissions using the <a href=":href_honeypot">honeypot</a> method.', $t_args),
  ];
  $form['third_party_settings']['honeypot']['honeypot'] = [
    '#type' => 'checkbox',
    '#title' => t('Protect %label with Honeypot', $t_args),
    '#default_value' => $honeypot,
  ];

  if ($honeypot_state != YAMLFORM_HONEYPOT_NEUTRAL) {
    $form['third_party_settings']['honeypot']['honeypot']['#attributes']['disabled'] = 'disabled';
    $form_state->set('honeypot_disabled', TRUE);
    if ($honeypot_state == YAMLFORM_HONEYPOT_ENABLED_ALL) {
      $form['third_party_settings']['honeypot']['honeypot']['#default_value'] = 1;
      $form['third_party_settings']['honeypot']['honeypot']['#description'] = t('<a href=":href_honeypot">Honeypot protection</a> is enabled for all forms.', $t_args);
    }
    elseif ($honeypot_state == YAMLFORM_HONEYPOT_ENABLED_YAMLFORM) {
      $form['third_party_settings']['honeypot']['honeypot']['#default_value'] = 1;
      $form['third_party_settings']['honeypot']['honeypot']['#description'] = t('<a href=":href_yamlform">Honeypot protection</a> is enabled for all forms.', $t_args);
    }
  }

  // Time limit.
  $form['third_party_settings']['honeypot']['time_restriction'] = [
    '#type' => 'checkbox',
    '#title' => t('Add time restriction to %label', $t_args),
    '#default_value' => $time_restriction,
  ];

  if ($time_restriction_state != YAMLFORM_HONEYPOT_NEUTRAL) {
    $form['third_party_settings']['honeypot']['time_restriction']['#attributes']['disabled'] = 'disabled';
    $form_state->set('time_restriction_disabled', TRUE);
    if ($time_restriction_state == YAMLFORM_HONEYPOT_DISABLED_ALL) {
      $form['third_party_settings']['honeypot']['time_restriction']['#default_value'] = 0;
      $form['third_party_settings']['honeypot']['time_restriction']['#description'] = t('<a href=":href_honeypot">Time limit</a> is disabled for all forms.', $t_args);
    }
    elseif ($time_restriction_state == YAMLFORM_HONEYPOT_ENABLED_YAMLFORM) {
      $form['third_party_settings']['honeypot']['time_restriction']['#default_value'] = 1;
      $form['third_party_settings']['honeypot']['time_restriction']['#description'] = t('<a href=":href_yamlform">Time limit</a> is enabled for all forms.', $t_args);
    }
  }

  $form['#validate'][] = '_yamlform_honeypot_form_validate';

}

/**
 * Validate callback; Checks if honeypot or time_restriction is disabled and removes them from the third party settings values.
 */
function _yamlform_honeypot_form_validate(&$form, FormStateInterface $form_state) {
  $third_party_settings = $form_state->getValue('third_party_settings');
  if ($form_state->get('honeypot_disabled')) {
    unset($third_party_settings['honeypot']['honeypot']);
  }
  if ($form_state->get('time_restriction_disabled')) {
    unset($third_party_settings['honeypot']['time_restriction']);
  }
  $form_state->setValue('third_party_settings', $third_party_settings);
}

/**
 * Implements hook_form_FORM_ID_alter().
 */
function honeypot_form_yamlform_admin_third_party_settings_form_alter(&$form, FormStateInterface $form_state) {
  /** @var \Drupal\yamlform\YamlFormThirdPartySettingsManagerInterface $third_party_settings_manager */
  $third_party_settings_manager = \Drupal::service('yamlform.third_party_settings_manager');

  $honeypot = $third_party_settings_manager->getThirdPartySetting('honeypot', 'honeypot');
  $honeypot_state = \Drupal::config('honeypot.settings')->get('protect_all_forms') ? YAMLFORM_HONEYPOT_ENABLED_ALL : YAMLFORM_HONEYPOT_NEUTRAL;

  $time_restriction = $third_party_settings_manager->getThirdPartySetting('honeypot', 'time_restriction');
  $time_restriction_state = (\Drupal::config('honeypot.settings')->get('time_limit') == 0) ? YAMLFORM_HONEYPOT_DISABLED_ALL : YAMLFORM_HONEYPOT_NEUTRAL;

  _yamlform_honeypot_form(
    $form,
    $form_state,
    $honeypot,
    $honeypot_state,
    $time_restriction,
    $time_restriction_state,
    t('all forms')
  );
}

/**
 * Implements hook_form_FORM_ID_alter().
 */
function honeypot_form_yamlform_third_party_settings_form_alter(&$form, FormStateInterface $form_state) {
  /** @var \Drupal\yamlform\YamlFormThirdPartySettingsManagerInterface $third_party_settings_manager */
  $third_party_settings_manager = \Drupal::service('yamlform.third_party_settings_manager');

  /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
  $yamlform = $form_state->getFormObject()->getEntity();

  $honeypot = $yamlform->getThirdPartySetting('honeypot', 'honeypot');
  if (\Drupal::config('honeypot.settings')->get('protect_all_forms')) {
    $honeypot_state = YAMLFORM_HONEYPOT_ENABLED_ALL;
  }
  elseif ($third_party_settings_manager->getThirdPartySetting('honeypot', 'honeypot')) {
    $honeypot_state = YAMLFORM_HONEYPOT_ENABLED_YAMLFORM;
  }
  else {
    $honeypot_state = YAMLFORM_HONEYPOT_NEUTRAL;
  }

  $time_restriction = $yamlform->getThirdPartySetting('honeypot', 'time_restriction');
  if (\Drupal::config('honeypot.settings')->get('time_limit') == 0) {
    $time_restriction_state = YAMLFORM_HONEYPOT_DISABLED_ALL;
  }
  elseif ($third_party_settings_manager->getThirdPartySetting('honeypot', 'time_restriction')) {
    $time_restriction_state = YAMLFORM_HONEYPOT_ENABLED_YAMLFORM;
  }
  else {
    $time_restriction_state = YAMLFORM_HONEYPOT_NEUTRAL;
  }

  _yamlform_honeypot_form(
    $form,
    $form_state,
    $honeypot,
    $honeypot_state,
    $time_restriction,
    $time_restriction_state,
    t('@label form', ['@label' => $yamlform->label()])
  );
}

/**
 * Implements hook_yamlform_submission_form_alter().
 */
function honeypot_yamlform_submission_form_alter(&$form, FormStateInterface $form_state, $form_id) {
  if (!empty($_POST)) {
    return;
  }

  /** @var \Drupal\yamlform\YamlFormThirdPartySettingsManagerInterface $third_party_settings_manager */
  $third_party_settings_manager = \Drupal::service('yamlform.third_party_settings_manager');

  /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
  $yamlform_submission = $form_state->getFormObject()->getEntity();
  $yamlform = $yamlform_submission->getYamlForm();

  $options = [];

  $honeypot = $third_party_settings_manager->getThirdPartySetting('honeypot', 'honeypot') ?:
    $yamlform->getThirdPartySetting('honeypot', 'honeypot');
  if ($honeypot) {
    $options[] = 'honeypot';
  }

  $time_restriction = $third_party_settings_manager->getThirdPartySetting('honeypot', 'time_restriction') ?:
    $yamlform->getThirdPartySetting('honeypot', 'time_restriction');
  if ($time_restriction) {
    $options[] = 'time_restriction';
  }

  if ($options) {
    honeypot_add_form_protection($form, $form_state, $options);
  }
}
