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

  /**
   * A mail manager for sending email.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;


  protected $queueFactory;

  /**
   * The configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

//  /**
//   * The token handler.
//   *
//   * @var \Drupal\Core\Utility\Token $token
//   */
//  protected $token;

//  }

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
//        $container->get('plugin.manager.mail'),
        $container->get('queue'),
        $container->get('config.factory')
//        $container->get('token')
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

    kint($yamlform_submission);
//    $token_data = [
//        'yamlform' => $yamlform_submission->getYamlForm(),
//        'yamlform-submission' => $yamlform_submission,
//        'yamlform-submission-options' => [
//            'email' => TRUE,
//            'excluded_inputs' => $this->configuration['excluded_inputs'],
//            'html' => ($this->configuration['html'] && $this->supportsHtml()),
//        ],
//    ];
//
//    $message = $this->configuration;
//    unset($message['excluded_inputs']);
//
//    // Replace 'default' values and [tokens] with settings default.
//    $settings = $this->getConfigurationSettings();
//    foreach ($settings as $setting_key => $setting) {
//      if (empty($message[$setting_key]) || $message[$setting_key] == 'default') {
//        $message[$setting_key] = $setting['default'];
//      }
//      $message[$setting_key] = $this->token->replace($message[$setting_key], $token_data);
//    }
//
//    // Trim the message body.
//    $message['body'] = trim($message['body']);
//
//    if ($this->configuration['html'] && $this->supportsHtml()) {
//      switch ($this->getMailSystemSender()) {
//        case 'swiftmailer':
//          // SwiftMailer requires that the body be valid Markup.
//          $message['body'] = Markup::create($message['body']);
//          break;
//      }
//    }

    $message = 'NULL';



    return $message;
  }

  /**
   * {@inheritdoc}
   */
  public function sendMessage(array $message) {
    kint($message);


//    // Send mail.
//    $to = $message['to_mail'];
//    $from = $message['from_mail'] . ' <' . $message['from_name'] . '>';
//    $current_langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
//    $this->mailManager->mail('yamlform', 'email.' . $this->getHandlerId(), $to, $current_langcode, $message, $from);
//
//    // Log message.
//    $variables = [
//        '@from_name' => $message['from_name'],
//        '@from_mail' => $message['from_mail'],
//        '@to_mail' => $message['to_mail'],
//        '@subject' => $message['subject'],
//    ];
//    \Drupal::logger('yamlform.email')->notice('@subject sent to @to_mail from @from_name [@from_mail].', $variables);


  }

  /**
   * {@inheritdoc}
   */
  public function resendMessageForm(array $message) {

    kint($message);

//    $element = [];
//    $element['to_mail'] = [
//        '#type' => 'email',
//        '#title' => $this->t('To email'),
//        '#default_value' => $message['to_mail'],
//    ];
//    $element['from_mail'] = [
//        '#type' => 'email',
//        '#title' => $this->t('From email '),
//        '#required' => TRUE,
//        '#default_value' => $message['from_mail'],
//    ];
//    $element['from_name'] = [
//        '#type' => 'textfield',
//        '#title' => $this->t('From name'),
//        '#required' => TRUE,
//        '#default_value' => $message['from_name'],
//    ];
//    $element['subject'] = [
//        '#type' => 'textfield',
//        '#title' => $this->t('Subject'),
//        '#default_value' => $message['subject'],
//    ];
//    $body_format = ($this->configuration['html']) ? 'html' : 'text';
//    $element['body'] = [
//        '#type' => 'yamlform_codemirror_' . $body_format,
//        '#title' => $this->t('Message (@format)', ['@format' => ($this->configuration['html']) ? $this->t('HTML') : $this->t('Plain text')]),
//        '#rows' => 10,
//        '#required' => TRUE,
//        '#default_value' => $message['body'],
//    ];
//    $element['html'] = [
//        '#type' => 'value',
//        '#value' => $message['html'],
//    ];
//    $element['attachments'] = [
//        '#type' => 'value',
//        '#value' => $message['attachments'],
//    ];
//
//    // Display attached files.
//    if ($message['attachments']) {
//      $file_links = [];
//      foreach ($message['attachments'] as $attachment) {
//        $file_links[] = [
//            '#theme' => 'file_link',
//            '#file' => $attachment['file'],
//            '#prefix' => '<div>',
//            '#suffix' => '</div>',
//        ];
//      }
//      $element['files'] = [
//          '#type' => 'item',
//          '#title' => $this->t('Attachments'),
//          '#markup' => \Drupal::service('renderer')->render($file_links),
//      ];
//    }
//
//    return $element;
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


    // @todo refactor this

    // Debug.
    $form['debug'] = [
        '#type' => 'details',
        '#title' => $this->t('Debugging'),
        '#open' => $this->configuration['debug'] ? TRUE : FALSE,
    ];
    $form['debug']['debug'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Enable debugging'),
        '#description' => $this->t('If checked sent emails will be displayed onscreen to all users.'),
        '#default_value' => $this->configuration['debug'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
  }


}
