<?php

namespace Drupal\cr_email_signup\Form;

/**
 * Concrete implementation of Step One.
 */
class RegisterInterestSignUp extends SignUp {

  protected $transType = 'RegisterInterest';

  /**
   * Get the Form Identifier.
   */
  public function getFormId() {
    return 'cr_email_signup_register_interest_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQueueName() {
    return 'Register_Interest';
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
  // ``, ``, ``, ``
  /**
   * Fill a message for the queue service.
   *
   * @param array $append_message
   *     Message to append to queue.
   */
  protected function fillQmessage($append_message) {
    // Please refactor this because is disgusting.
    $append_message['timestamp'] = time();
    $append_message['transSourceURL'] = \Drupal::service('path.current')->getPath();
    $append_message['transSource'] = "{$this->campaign['campaign']}_[Device]_ESU_[PageElementSource]";

    // RND-178: Device & Source Replacements.
    $device = (empty($append_message['device'])) ? "Unknown" : $append_message['device'];
    $source = (empty($append_message['source'])) ? "Unknown" : $append_message['source'];

    $append_message['transSource'] = str_replace(
      ['[Device]', '[PageElementSource]'],
      [$device, $source],
      $append_message['transSource']
    );

    // Add passed arguments.
    $append_message['campaign'] = $this->campaign;
    $append_message['transType'] = $this->transType;
    $append_message['first_name'] = '';
    $append_message['last_name'] = '';
    $append_message['postcode'] = '';
    $append_message['What_events_are_you_interested_in'] = '';

    $this->sendQmessage($append_message);
  }

}
