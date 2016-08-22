<?php

namespace Drupal\cr_email_signup\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Concrete implementation of Step One.
 */
class WorkplaceSignUp extends SignUp {

  /**
   * Get the Form Identifier.
   */
  public function getFormId() {
    return 'cr_email_signup_workplace_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQueueName() {
    return 'esu_workplace';
  }

  protected function EsuContentFields() {
    $form['firstName'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your first name'),
      '#placeholder' => $this->t('Enter your first name'),
    ];
    return $form;
  }

}
