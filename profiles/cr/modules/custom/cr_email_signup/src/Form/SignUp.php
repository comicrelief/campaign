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
    $queue_message = array_merge($this->skeletonMessage, $append_message);

    // TODO: Move to config/default.
    $queue_name = 'queue1';
    $queue_factory = \Drupal::service('queue');
    $queue = $queue_factory->get($queue_name);
    $queue->createItem($queue_message);
  }

  /**
   * Build the Form Elements.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form_state = $form_state;

/*    $form['#prefix'] = '<div class="some-old-bollocks">';
    $form['#suffix'] = '</div>';*/

    $form['email'] = array(
      '#type' => 'email',
      '#title' => $this->t('Your email address'),
      '#prefix'=> '<div class="cr-email-signup__email-wrapper">',
    );

    /* Set our first step class, to be updated by JS */
    $form['#attributes']['class'][] = "cr-email-signup-form--step-1";

    $form['send_email'] = array(
      '#type' => 'button',
      '#name' => 'send_email',
      '#value' => t('Go'),
      '#ajax' => array(
        'callback' => array($this, 'queueEmail'),
        'progress' => array(
          'type' => 'bar',
          'message' => "",
        ),
      ),
      '#suffix'=>'</div>',
    );

    $form['school_phase'] = array(
      '#type' => 'select',
      '#title' => $this->t('School Phase'),
      '#options' => array(
        0 => ' -- Select age group --',
        'EY' => 'Early Years or Nursery',
        'PY' => 'Primary',
        'SY' => 'Secondary',
        'FE' => 'Further Education or Sixth-Form College',
        'HE' => 'Higher Education',
        'OH' => 'Other',
      ),
      '#prefix'=> '<div class="cr-email-signup__school-phase-wrapper">',
    );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
      '#weight' => 10,
      '#suffix'=>'</div>',
    );

    return $form;
  }

  /**
   * Send the first email message to the queue.
   */
  public function queueEmail(&$form, FormStateInterface $form_state) {
    $email_address = $form_state->getValue('email');

    $queue_message = array(
      'transSourceURL' => \Drupal::service('path.current')->getPath(),
      'transSource' => "{$this->skeletonMessage['campaign']}_[Device]_ESU_[PageElementSource]",
      'timestamp' => time(),
      'emailAddress' => $email_address,
    );
    $this->queueMessage($queue_message);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $email_address = $form_state->getValue('email');
    $school_phase = $form_state->getValue('school_phase');
    // TODO: make key transSource dynamic/configurable.
    $queue_message = array(
      'transSourceURL' => \Drupal::service('path.current')->getPath(),
      'transSource' => "{$this->skeletonMessage['campaign']}_[Device]_ESU_[PageElementSource]",
      'timestamp' => time(),
      'emailAddress' => $email_address,
      'schoolPhase' => $school_phase,
    );

    $this->queueMessage($queue_message);

    drupal_set_message($this->t("Great! Now we know what's right for you"));

    return TRUE;
  }

  function cr_email_signup_theme($existing, $type, $theme, $path) {
    dpm( "MEH" );
  }

}
