<?php

namespace Drupal\cr_email_signup\Form;

/**
 * Concrete implementation of Step One.
 */
class FundraiseSignUp extends SignUp {

  protected $esulist = ['listname' => ['general']];

  /**
   * Get the Form Identifier.
   */
  public function getFormId() {
    return 'cr_email_signup_fundraise_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQueueName() {
    return 'esu';
  }

  /**
   * {@inheritdoc}
   */
  protected function esuSubmitFields() {
    $form['step1'] = [
      '#type' => 'button',
      '#name' => 'step1',
      '#value' => $this->t('Sign Up'),
      '#attributes' => ['class' => ['step1']],
      '#ajax' => [
        'callback' => [$this, 'processSteps'],
      ],
    ];
    return $form;
  }

}
