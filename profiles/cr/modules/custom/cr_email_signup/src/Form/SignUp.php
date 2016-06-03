<?php
/**
 * @file
 * Contains \Drupal\cr_email_signup\Form\SignUp.
 */

namespace Drupal\cr_email_signup\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
/**
 * Concrete implementation of Step One.
 */
class SignUp extends FormBase implements FormInterface {

  /**
   * Array to send to queue. Some key values should be sourced from config.
   *
   * @var array
   *     Skeleton message to send
   */
  protected $skeletonMessage = array(
    // todo: should this be hardcoded??
    'campaign' => 'RND17',
    'transType' => 'esu',
    'timestamp' => NULL,
    'transSourceURL' => NULL,
    'transSource' => NULL,
    'emailAddress' => NULL,
  );

  /**
   * Get the Form Identifier.
   */
  public function getFormId() {

    return 'cr_email_signup_form';
  }

  /**
   * Send a message to the queue service.
   *
   * @param array $append_message
   *     Message to append to queue.
   */
  protected function queueMessage($append_message) {
    // Add dynamic keys.
    $append_message['timestamp'] = time();
    $append_message['transSourceURL'] = \Drupal::service('path.current')->getPath();
    $append_message['transSource'] = "{$this->skeletonMessage['campaign']}_[Device]_ESU_[PageElementSource]";

    // Add passed arguments.
    $queue_message = array_merge($this->skeletonMessage, $append_message);

    // TODO: Move to config/default.
    $queue_name = 'esu';
    try {
      $queue_factory = \Drupal::service('queue');
      $queue = $queue_factory->get($queue_name);
      if (FALSE === $queue->createItem($queue_message)) {
        throw new \Exception("createItem Failed. Check Queue.");
      }
    }
    catch (\Exception $exception) {
      \Drupal::logger('cr_email_signup')->error("Unable to queue message. Attempted to queue message '$queue_message'. Error was: " . $exception->getMessage());
    }
  }

  /**
   * Build the Form Elements.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form_state = $form_state;

    $form['steps'] = array(
      '#prefix' => '<div class="esu-signup-form-step1" id="esu-signup-form-step1-id">',
      '#suffix' => '</div>',
    );

    $form['steps']['email'] = array(
      '#type' => 'email',
      "#required" => TRUE,
      '#title' => $this->t('Your email address'),
      '#placeholder' => t('Enter your email address'),
      '#prefix' => '<div class="cr-email-signup__email-wrapper">',
      '#suffix' => '</div>',
    );

    $form['steps']['school_phase'] = array(
      '#type' => 'select',
      '#title' => $this->t('Also send me School resources'),
      '#options' => array(
        0 => ' -- Select age group --',
        'EY' => 'Early Years or Nursery',
        'PY' => 'Primary',
        'SY' => 'Secondary',
        'FE' => 'Further Education or Sixth-Form College',
        'HE' => 'Higher Education',
        'OH' => 'Other',
      ),
      '#prefix' => '<div class="cr-email-signup__school-phase-wrapper">',
      '#suffix' => '</div>',
    );

    $form['steps']['validate_email'] = array(
      '#prefix' => '<div class="cr-email-signup__submit-wrapper">',
      '#suffix' => '</div>',
      '#type' => 'submit',
      '#name' => 'validate_email',
      '#value' => t('Go'),
      '#ajax' => array(
        'callback' => array($this, 'validateAndQueue'),
        'progress' => array(
          'type' => '',
          'message' => "",
        ),
        'prevent' => 'submit',
        'wrapper' => 'esu-signup-form-step1-id',
        'event' => 'mouseup',
      ),
    );

    return $form;
  }

  /**
   * Custom validate function.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email_address = $form_state->getValue('email');
    $school_phase = $form_state->getValue('school_phase');
    $email_valid = \Drupal::service('email.validator')->isValid($email_address);

    if (!empty($email_address) && $email_valid && empty($school_phase)) {
      // On to step 2. Nothing for now.
    }
    else {
      // Not even sure this needs to be here?
      parent::validateForm($form, $form_state);
    }

    return $form;
  }

  /**
   * Validate current inputs and queue if possible.
   */
  public function validateAndQueue(array &$form, FormStateInterface $form_state) {
    $email_address = $form_state->getValue('email');
    $school_phase = $form_state->getValue('school_phase');
    $email_valid = \Drupal::service('email.validator')->isValid($email_address);

    if (!empty($email_address) && $email_valid && !empty($school_phase)) {
      // Clear first steps.
      unset($form['steps']['email']);
      unset($form['steps']['school_phase']);
      unset($form['steps']['validate_email']);

      // Queue the message with both email and school phase.
      $this->queueMessage(array(
        'emailAddress' => $email_address,
        'schoolPhase' => $school_phase,
      ));
    }
    elseif (!empty($email_address) && $email_valid && empty($school_phase)) {
      // Queue the message with only the email available.
      $this->queueMessage(array(
        'emailAddress' => $email_address,
      ));
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Only here for completeness, should not be called.
    return TRUE;
  }

}
