<?php

/**
 * @file
 * Contains \Drupal\rnd_preorder\Plugin\YamlFormHandler\RabbitMQYamlFormHandler.
 */

namespace Drupal\rnd_preorder\Plugin\YamlFormHandler;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Utility\Token;
use Drupal\file\Entity\File;
use Drupal\yamlform\YamlFormHandlerBase;
use Drupal\yamlform\YamlFormHandlerMessageInterface;
use Drupal\yamlform\YamlFormSubmissionInterface;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Emails a YAML form submission.
 *
 * @YamlFormHandler(
 *   id = "rabbitmq",
 *   label = @Translation("RabbitMQ"),
 *   description = @Translation("Submits submission to a RabbitMQ"),
 *   cardinality = \Drupal\yamlform\YamlFormHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\yamlform\YamlFormHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class RabbitMQYamlFormHandler extends YamlFormHandlerBase implements YamlFormHandlerMessageInterface {


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
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getMessage(YamlFormSubmissionInterface $yamlform_submission) {
    // Fetch all data, we ship this off to the queue
    $message = $yamlform_submission->getData();

    // @todo clean this up - if needed at all?
    unset($message['in_draft']);

    return $message;
  }

  /**
   * {@inheritdoc}
   */
  public function sendMessage(array $message) {

    // @todo parametrize this queue name
    $queue_name = 'queue1';
    $queue = $this->queueFactory->get($queue_name);
    $queue->createItem($message);

    $variables = [
      '@queue' => $queue_name,
    ];
    \Drupal::logger('yamlform.rabbitmq')->notice('Data package sent to queue @queue', $variables);
  }

  /**
   * {@inheritdoc}
   */
  public function resendMessageForm(array $message) {
    // @todo implement this, is this needed?
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
      '#type' => 'textfield',
      '#title' => $this->t('Queue name'),
      '#default_value' => $this->configuration['queue_name'],
    ];

    // @todo refactor this
    // Add queue options

    // Debug.
    // $form['debug'] = [
    //     '#type' => 'details',
    //     '#title' => $this->t('Debugging'),
    //     '#open' => $this->configuration['debug'] ? TRUE : FALSE,
    // ];
    // $form['debug']['debug'] = [
    //     '#type' => 'checkbox',
    //     '#title' => $this->t('Enable debugging'),
    //     '#description' => $this->t('If checked sent emails will be displayed onscreen to all users.'),
    //     '#default_value' => $this->configuration['debug'],
    // ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
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
