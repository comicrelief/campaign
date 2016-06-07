<?php
/**
 * @file
 * Contains \Drupal\cr_email_signup\Form\SignUp.
 */

namespace Drupal\cr_email_signup\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * Concrete implementation of Step One.
 */
class SignUp extends FormBase implements FormInterface {

  /**
   * Private temporary storage factory.
   *
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Session manager.
   *
   * @var \Drupal\Core\Session\SessionManagerInterface
   */
  private $sessionManager;

  /**
   * Current User.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $currentUser;

  /**
   * The actual storage container.
   *
   * @var \Drupal\user\PrivateTempStore
   */
  protected $store;

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
   * Constructs a \Drupal\demo\Form\Multistep\MultistepFormBase.
   *
   * @param \Drupal\user\PrivateTempStoreFactory $temp_store_factory
   *   Injected Private temporary storage factory.
   * @param \Drupal\Core\Session\SessionManagerInterface $session_manager
   *   Injected Session manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Injected Current user.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, SessionManagerInterface $session_manager, AccountInterface $current_user) {
    $this->tempStoreFactory = $temp_store_factory;
    $this->sessionManager = $session_manager;
    $this->currentUser = $current_user;

    $this->store = $this->tempStoreFactory->get('esu_state');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('user.private_tempstore'),
      $container->get('session_manager'),
      $container->get('current_user')
    );
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
    // Start a manual session for anonymous users.
    if ($this->currentUser->isAnonymous() && !isset($_SESSION['esu_session'])) {
      $_SESSION['esu_session'] = TRUE;
      $this->sessionManager->start();
    }

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
    $email_queued = $this->store->get('email_queued');
    $email_valid = \Drupal::service('email.validator')->isValid($email_address) && strlen($email_address) <= 100;

    if (!empty($email_address)) {
      if (!$email_valid) {
        $form_state->setErrorByName('email', 'Please enter a valid email address.');
      }
      elseif (empty($school_phase) && $email_queued) {
        // Email looks valid but no school phase selected, make it required.
        $form_state->setErrorByName('school_phase', 'Please select an age group.');
        $form['steps']['school_phase']['#required'] = TRUE;
      }
    }
    else {
      // This is only for completeness, should be picked up before submit.
      $form_state->setErrorByName('email', 'Please enter a valid email address.');
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

      // Once we've queued a full ESU, delete the temp storage.
      // Might be something to turn off on production.
      $this->store->delete('email_queued');
    }
    elseif (!empty($email_address) && $email_valid && empty($school_phase)) {
      // Queue the message with only the email available.
      $this->queueMessage(array(
        'emailAddress' => $email_address,
      ));

      // Store that ONLY an email has been queued.
      $this->store->set('email_queued', TRUE);
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
