<?php
/**
 * @file
 * Contains \Drupal\cr_multistep_form\Form\MultiStepFormBase.
 */

namespace Drupal\cr_email_signup\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * Abstract Implementation of Base Form.
 */
abstract class MultiStepFormBase extends FormBase {

  /**
   * Temporary Store Factory.
   *
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Session Manager Interface.
   *
   * @var \Drupal\Core\Session\SessionManagerInterface
   */
  private $sessionManager;

  /**
   * Account Interface.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $currentUser;

  /**
   * Private Temporary Store.
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
    'campaign' => 'RND17',
    'transType' => 'esu',
    'timestamp' => NULL,
    'transSourceURL' => NULL,
    'transSource' => NULL,
    'emailAddress' => NULL,
  );

  /**
   * Constructs a \Drupal\cr_email_signup\Form\Multistep\MultistepFormBase.
   *
   * @param \Drupal\user\PrivateTempStoreFactory $temp_store_factory
   *   Temporary Stor Factory.
   * @param \Drupal\Core\Session\SessionManagerInterface $session_manager
   *   Session Manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current User.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, SessionManagerInterface $session_manager, AccountInterface $current_user) {
    $this->tempStoreFactory = $temp_store_factory;
    $this->sessionManager = $session_manager;
    $this->currentUser = $current_user;

    $this->store = $this->tempStoreFactory->get('multistep_data');
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
   * Build Form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Quieten phpmd by touching $form_state. Required by definition.
    $form_state = (array) $form_state;
    // Start a manual session for anonymous users.
    if ($this->currentUser->isAnonymous() && !isset($_SESSION['multistep_form_holds_session'])) {
      $_SESSION['multistep_form_holds_session'] = TRUE;
      $this->sessionManager->start();
    }

    $form = array();
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
      '#weight' => 10,
    );

    return $form;
  }

  /**
   * Saves the data from the multistep form.
   */
  protected function saveData() {
    $this->deleteStore();
    drupal_set_message($this->t("Great! Now we know what's right for you"));

  }

  /**
   * Helper method that removes all known keys.
   */
  protected function deleteStore() {
    $keys = ['email', 'school_phase'];
    foreach ($keys as $key) {
      $this->store->delete($key);
    }
  }

}
