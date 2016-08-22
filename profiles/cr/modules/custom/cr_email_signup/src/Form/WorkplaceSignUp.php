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

  /**
   * Build the Form Elements.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your email address'),
      '#placeholder' => $this->t('Enter your email address'),
    ];
    $form['firstName'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your first name'),
      '#placeholder' => $this->t('Enter your first name'),
    ];
    $form['device'] = [
      '#name' => 'device',
      '#type' => 'hidden',
      '#attributes' => [
        'id' => 'esu-device',
      ],
    ];
    $form['source'] = [
      '#name' => 'source',
      '#type' => 'hidden',
      '#attributes' => [
        'id' => 'esu-source',
      ],
    ];

    $form['step1'] = [
      '#type' => 'button',
      '#name' => 'step1',
      '#value' => $this->t('Go'),
      '#attributes' => ['class' => ['step1']],
      '#ajax' => [
        'callback' => [$this, 'processSteps'],
      ],
    ];

    return $form;
  }

}
