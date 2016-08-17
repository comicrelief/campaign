<?php

namespace Drupal\cr_email_signup\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;

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
    $form_state = $form_state;
    $form['steps']['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your email address'),
      '#placeholder' => $this->t('Enter your email address'),
    ];
    $form['steps']['device'] = [
      '#name' => 'device',
      '#type' => 'hidden',
      '#attributes' => array(
        'id' => 'esu-device',
      ),
    ];
    $form['steps']['source'] = [
      '#name' => 'source',
      '#type' => 'hidden',
      '#attributes' => array(
        'id' => 'esu-source',
      ),
    ];

    $form['steps']['step1'] = [
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
