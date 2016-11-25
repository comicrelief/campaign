<?php

namespace Drupal\cr_email_signup\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\PrependCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\cr_email_signup\MessageQueue\Sender;

/**
 * Generate Email Sign up.
 */
abstract class SignUp extends FormBase {

  public static $ERRORS = [
    'MAIL' => 'error--email',
    'NAME' => 'error--firstname',
    'AGEGROUP' => 'error--agegroup',
    'ESU' => 'block--cr-email-signup--error',
  ];

  // Convert all this small variables into a class.
  protected $transType = 'esu';
  protected $esulist = ['listname' => ['general']];
  protected $queue_name = 'esu';

  /**
   * Build the Form Elements.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Annoy code check!
    $form_state = $form_state;

    $form += $this->esuRequiredFields();
    $form += $this->esuContentFields();
    $form += $this->esuSubmitFields();

    return $form;
  }

  /**
   * Build the mandatory fields of the form.
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
      '#attributes' => [
        'class' => ['–metrika-nokeys'],
      ],
    ];
    return $form;
  }

  /**
   * Build the extra elements of the form.
   */
  protected function esuContentFields() {
    return [];
  }

  /**
   * Build the submit elements.
   */
  protected function esuSubmitFields() {
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

  /**
   * Return class of the form.
   */
  private function getClassId() {
    return '.' . str_replace('_', '-', $this->getFormId());
  }

  /**
   * Validates all fields.
   */
  private function validateFields(
    FormStateInterface $form_state,
    AjaxResponse $response
  ) {
    $pass = TRUE;
    $exist_field_name = $form_state->hasValue('firstName');
    $name_is_empty = $form_state->isValueEmpty('firstName');
    $email = $form_state->getValue('email');
    $valid_email = \Drupal::service('email.validator')->isValid($email);

    $this->cleanStatusMessage($response);
    if (!$valid_email) {
      $this->setErrorMessage(
        $response,
        self::$ERRORS['MAIL'],
        'Please enter a valid email address.'
      );
      $pass = FALSE;
    }
    if ($exist_field_name && $name_is_empty) {
      $this->setErrorMessage(
        $response,
        self::$ERRORS['NAME'],
        'Please enter your name.'
      );
      $pass = FALSE;
    }

    return $pass;
  }

  /**
   * Process form steps.
   */
  public function processSteps(array &$form, FormStateInterface $form_state) {
    // Annoy code check!
    $form = $form;
    $triggering_element = $form_state->getTriggeringElement();
    $response = new AjaxResponse();
    switch ($triggering_element['#name']) {
      case 'step1':
        if ($this->validateFields($form_state, $response)) {
          // @TODO: Refactor this!
          $data = [
            'email' => $form_state->getValue('email'),
            'device' => $form_state->getValue('device'),
            'source' => $form_state->getValue('source'),
          ];
          if (!empty($this->esulist)) {
            $data['subscribeLists'] = $this->esulist;
          }
          if ($form_state->getValue('firstName')) {
            $data['firstName'] = $form_state->getValue('firstName');
          }
          $data['transType'] = $this->transType;
          $sender = new Sender();
          $sender->sendTo($this->queue_name, $data);
          $this->nextStep($response, 1);
        }
        break;

      case 'step2':
        $email = $form_state->getValue('email');
        $this->esulist = ['listname' => ['teacher']];
        $valid_email = \Drupal::service('email.validator')->isValid($email);
        if (!$form_state->isValueEmpty('school_phase') && $valid_email) {
          $sender = new Sender();
          $sender->sendTo($this->queue_name, [
            'email' => $form_state->getValue('email'),
            'phase' => $form_state->getValue('school_phase'),
            'device' => $form_state->getValue('device'),
            'source' => $form_state->getValue('source'),
            'subscribeLists' => $this->esulist,
          ]);
          $this->nextStep($response, 2);

        }
        else {
          $this->setErrorMessage(
            $response,
            self::$ERRORS['AGEGROUP'],
            'Please select an age group.'
          );
        }
        break;
    }
    // Return ajax response.
    return $response;
  }

  /**
   * Clean the message.
   */
  private function cleanStatusMessage(AjaxResponse $response) {
    $response->addCommand(new HtmlCommand('.esu-errors', ''));
  }

  /**
   * Go to the next step of the multiform.
   */
  private function nextStep(AjaxResponse $response, $step) {
    $this->cleanStatusMessage($response);
    foreach (self::$ERRORS as $classname) {
      $response->addCommand(new InvokeCommand(
        $this->getClassId(),
        'removeClass',
        [$classname]
      ));
    }
    $response->addCommand(new InvokeCommand(
      $this->getClassId(),
      'removeClass',
      ['block--cr-email-signup--step-' . $step]
    ));
    $response->addCommand(new InvokeCommand(
      $this->getClassId(),
      'addClass',
      ['block--cr-email-signup--step-' . ($step + 1)]
    ));
  }

  /**
   * Set the error message.
   */
  private function setErrorMessage(AjaxResponse $response, $class, $message) {
    // Error if validation isnt met.
    $response->addCommand(new PrependCommand(
      '.esu-errors', $message
    ));
    $response->addCommand(new InvokeCommand(
      $this->getClassId(),
      'addClass',
      [self::$ERRORS['ESU']]
    ));
    $response->addCommand(new InvokeCommand(
      $this->getClassId(),
      'addClass',
      [$class]
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    return $form;
  }

}
