<?php

namespace Drupal\cr_email_signup\Form;

/**
 * Concrete implementation of Step One.
 */
class WorkplaceSignUp extends SignUp {

  protected $transType = 'WorkplaceESU';
  protected $esulist = ['general' => 'general'];

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
    $form['firstName'] = [
      '#type' => 'textfield',
      '#maxlength' => 100,
      '#title' => $this->t('Your first name'),
      '#placeholder' => $this->t('Enter your first name'),
      '#attributes' => [
        'class' => ['–metrika-nokeys'],
      ],
    ];
    $form['email'] = [
      '#type' => 'textfield',
      '#maxlength' => 500,
      '#title' => $this->t('Your email address'),
      '#placeholder' => $this->t('Enter your email address'),
      '#attributes' => [
        'class' => ['–metrika-nokeys'],
      ],
    ];
    return $form;
  }

}
