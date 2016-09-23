<?php

namespace Drupal\cr_email_signup\Form;

/**
 * Concrete implementation of Step One.
 */
class FundraiseSignUp extends SignUp {

  protected $transType = 'FundraiseESU';
  protected $esulist = ['listname' => 'fundraise'];

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

  /**
   * {@inheritdoc}
   */
  protected function esuRequiredFields() {
    $form['device'] = [
      '#name' => 'device',
      '#type' => 'hidden',
      '#attributes' => [
        'class' => 'esu-device',
      ],
    ];
    $form['source'] = [
      '#name' => 'source',
      '#type' => 'hidden',
      '#attributes' => [
        'class' => 'esu-source',
      ],
    ];
    $form['email'] = [
      '#type' => 'textfield',
      '#maxlength' => 500,
      '#title' => $this->t('Your email address'),
      '#placeholder' => $this->t('Enter your email address'),
    ];
    return $form;
  }

}
