<?php

/**
 * @file
 * Contains \Drupal\cr_transactional_emails\Form\TransactionalEmailsSettingsTestForm.
 */

namespace Drupal\cr_transactional_emails\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Test module settings for this site.
 */
class TransactionalEmailsSettingsTestForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cr_transactional_emails_admin_settings_test';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Build form - send to address and template keys.
    $form = array();

    $form['help_intro'] = array(
      '#markup' => '<p>' . t('Test API connection, trigger action and template.') . '</p>',
    );

    $form['smartfocus'] = array(
      '#type' => 'fieldset',
      '#title' => t('SmartFocus'),
      '#attributes' => array('class' => array('trans-email-admin-settings')),
    );

    $form['smartfocus']['send_to'] = array(
      '#type' => 'email',
      '#title' => t('Send to:'),
      '#required' => TRUE,
    );

    $form['smartfocus']['template_unique_api_key'] = array(
      '#type' => 'textfield',
      '#title' => t('Template unique key'),
      '#required' => TRUE,
    );

    $form['smartfocus']['template_security_api_key'] = array(
      '#type' => 'textfield',
      '#title' => t('Template security key'),
      '#required' => TRUE,
    );

    $form['smartfocus']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    );

    // Return form structure.
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Grab form values.
    $send_to = $form_state->getValue('send_to');
    $template = array(
      'template_unique_api_key' => $form_state->getValue('template_unique_api_key'),
      'template_security_api_key' => $form_state->getValue('template_security_api_key'),
    );
    $params = array();

    // Send action to email service provider and grab status.
    $status = cr_transactional_emails_send($send_to, $template, $params);

    // Manage status returned from action performed and manage form.
    // Reset form on success; rebuild form on error.
    if ($status) {
      $form_state->setRebuild(FALSE);
      drupal_set_message('Trigger action sent', 'status');
    }
    else {
      $form_state->setRebuild(TRUE);
      drupal_set_message('There was an issue triggering the action - please review the logs', 'error');
    }
  }

}
