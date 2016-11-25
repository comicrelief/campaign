<?php

namespace Drupal\cr_email_signup\Form;

/**
 * Concrete implementation of Step One.
 */
class RegisterInterestSignUp extends SignUp {

  protected $transType = 'RegisterInterest';
  protected $esulist = '';
  protected $queue_name = 'register_interest';

  /**
   * Get the Form Identifier.
   */
  public function getFormId() {
    return 'cr_email_signup_register_interest_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function esuSubmitFields() {
    $form['step1'] = [
      '#type' => 'button',
      '#name' => 'step1',
      '#value' => $this->t('Subscribe'),
      '#attributes' => ['class' => ['step1']],
      '#ajax' => [
        'callback' => [$this, 'processSteps'],
      ],
    ];
    return $form;
  }

}
