<?php

/**
 * @file
 * Contains \Drupal\yamlform_queue\Plugin\YamlFormHandler\QueueYamlFormHandler.
 */

namespace Drupal\yamlform_queue\Plugin\YamlFormHandler;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\yamlform\YamlFormHandlerBase;
use Drupal\yamlform\YamlFormHandlerMessageInterface;
use Drupal\yamlform\YamlFormSubmissionInterface;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Send a YAML form submission to a queue.
 *
 * @YamlFormHandler(
 *   id = "queue",
 *   label = @Translation("Queue"),
 *   description = @Translation("Sends form submissions to a queue"),
 *   cardinality = \Drupal\yamlform\YamlFormHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\yamlform\YamlFormHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class QueueYamlFormHandler extends YamlFormHandlerBase implements YamlFormHandlerMessageInterface {

  /**
   * The queue factory.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * The configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerInterface $logger, QueueFactory $queue_factory, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $logger);
    $this->queueFactory = $queue_factory;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory')->get('yamlform'),
      $container->get('queue'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'queue_name' => '',
      'debug' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getMessage(YamlFormSubmissionInterface $yamlform_submission) {
    // Fetch all data, we ship this off to the queue
    $message = $yamlform_submission->getData();

    // Remove message elements
    unset($message['in_draft']);

    return $message;
  }

  /**
   * {@inheritdoc}
   */
  public function sendMessage(array $message) {
    try {
      // Create a queue
      $queue = $this->queueFactory->get($this->configuration['queue_name']);

      // Send the message
      $queue->createItem($message);

      // Log message
      $variables = [
        '@queue' => $this->configuration['queue_name'],
        '@data' => implode(',', $message),
      ];

      \Drupal::logger('yamlform.queue')->notice('Data package sent to queue @queue', $variables);

      // Debug by displaying onscreen.
      if ($this->configuration['debug']) {
        $output = $this->t('Following data has been sent to queue @queue: @data', $variables);
        drupal_set_message($output, 'warning');
      }
    }
    // @todo fix exception catching
    catch (EntityStorageException $e) {
      watchdog_exception('yamlform.queue', $e);
    }
  }

  /**
   * Get queue configuration values.
   *
   * @return array
   *   An associative array containing queue configuration values.
   */
  protected function getQueueConfiguration() {
    $configuration = $this->getConfiguration();
    $settings = $configuration['settings'];

    // Get queue so we can check the queue type
    $queue = $this->queueFactory->get($this->configuration['queue_name']);
    $settings['queue_class'] = get_class($queue);

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function resendMessageForm(array $message) {
    // @todo implement resending, currently not allowed.
  }

  /**
   * {@inheritdoc}
   */
  public function getMessageSummary(array $message) {
    return [
      '#settings' => $message,
    ] + parent::getSummary();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // Settings.
    $form['settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Settings'),
      '#open' => TRUE,
    ];
    $form['settings']['queue_name'] = [
      '#type' => 'yamlform_codemirror_text',
      '#title' => $this->t('Queue name'),
      '#description' => $this->t('The machine name of the queue to use. The queue will be created if it does not exist yet.'),
      '#default_value' => $this->configuration['queue_name'],
    ];

    // Debug.
    $form['debug'] = [
      '#type' => 'details',
      '#title' => $this->t('Debugging'),
      '#open' => $this->configuration['debug'] ? TRUE : FALSE,
    ];
    $form['debug']['debug'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable debugging'),
      '#description' => $this->t('If checked, data sent to the queue will also be displayed onscreen to all users.'),
      '#default_value' => $this->configuration['debug'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['queue_name'] = $values['settings']['queue_name'];
    $this->configuration['debug'] = $values['debug']['debug'];
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    return [
      '#settings' => $this->getQueueConfiguration(),
    ] + parent::getSummary();
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(YamlFormSubmissionInterface $yamlform_submission, $update = TRUE) {
    if ($yamlform_submission->getState() == YamlFormSubmissionInterface::STATE_COMPLETED) {
      $message = $this->getMessage($yamlform_submission);
      $this->sendMessage($message);
    }
  }

}
