<?php

namespace Drupal\yamlform\Plugin\YamlFormHandler;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Utility\Token;
use Drupal\file\Entity\File;
use Drupal\yamlform\Element\YamlFormSelectOther;
use Drupal\yamlform\YamlFormHandlerBase;
use Drupal\yamlform\YamlFormHandlerMessageInterface;
use Drupal\yamlform\YamlFormSubmissionInterface;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Emails a form submission.
 *
 * @YamlFormHandler(
 *   id = "email",
 *   label = @Translation("Email"),
 *   category = @Translation("Notification"),
 *   description = @Translation("Sends a form submission via an email."),
 *   cardinality = \Drupal\yamlform\YamlFormHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\yamlform\YamlFormHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class EmailYamlFormHandler extends YamlFormHandlerBase implements YamlFormHandlerMessageInterface {

  /**
   * A mail manager for sending email.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The token handler.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * Cache of default configuration values.
   *
   * @var array
   */
  protected $defaultValues;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerInterface $logger, MailManagerInterface $mail_manager, ConfigFactoryInterface $config_factory, Token $token) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $logger);
    $this->mailManager = $mail_manager;
    $this->configFactory = $config_factory;
    $this->token = $token;
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
      $container->get('plugin.manager.mail'),
      $container->get('config.factory'),
      $container->get('token')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    return [
      '#settings' => $this->getEmailConfiguration(),
    ] + parent::getSummary();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'to_mail' => 'default',
      'cc_mail' => '',
      'bcc_mail' => '',
      'from_mail' => 'default',
      'from_name' => 'default',
      'subject' => 'default',
      'body' => 'default',
      'excluded_elements' => [],
      'html' => TRUE,
      'attachments' => FALSE,
      'debug' => FALSE,
    ];
  }

  /**
   * Get configuration default values.
   *
   * @return array
   *   Configuration default values.
   */
  protected function getDefaultConfigurationValues() {
    if (isset($this->defaultValues)) {
      return $this->defaultValues;
    }

    $yamlform_settings = $this->configFactory->get('yamlform.settings');
    $site_settings = $this->configFactory->get('system.site');
    $body_format = ($this->configuration['html']) ? 'html' : 'text';
    $default_mail = $yamlform_settings->get('mail.default_to_mail') ?: $site_settings->get('mail') ?: ini_get('sendmail_from');

    $this->defaultValues = [
      'to_mail' => $default_mail,
      'cc_mail' => $default_mail,
      'bcc_mail' => $default_mail,
      'from_mail' => $default_mail,
      'from_name' => $yamlform_settings->get('mail.default_from_name') ?: $site_settings->get('name'),
      'subject' => $yamlform_settings->get('mail.default_subject') ?: 'Form submission from: [yamlform-submission:source-entity]',
      'body' => $this->getBodyDefaultValues($body_format),
    ];

    return $this->defaultValues;
  }

  /**
   * Get configuration default value.
   *
   * @param string $name
   *   Configuration name.
   *
   * @return string|array
   *   Configuration default value.
   */
  protected function getDefaultConfigurationValue($name) {
    $default_values = $this->getDefaultConfigurationValues();
    return $default_values[$name];
  }

  /**
   * Get mail configuration values.
   *
   * @return array
   *   An associative array containing email configuration values.
   */
  protected function getEmailConfiguration() {
    $configuration = $this->getConfiguration();
    $email = [];
    foreach ($configuration['settings'] as $key => $value) {
      if ($value === 'default') {
        $email[$key] = $this->getDefaultConfigurationValue($key);
      }
      else {
        $email[$key] = $value;
      }
    }
    return $email;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $mail_element_options = [];
    $text_element_options = [];
    $elements = $this->yamlform->getElementsInitializedAndFlattened();
    foreach ($elements as $key => $element) {
      $title = (isset($element['#title'])) ? new FormattableMarkup('@title (@key)', ['@title' => $element['#title'], '@key' => $key]) : $key;
      if (isset($element['#type']) && in_array($element['#type'], ['email', 'hidden', 'value', 'select', 'radios', 'textfield', 'yamlform_email_multiple', 'yamlform_email_confirm'])) {
        // Note: Token must use the :raw form mail elements.
        // For example a select menu's option value would be used to route an
        // email address.
        $mail_element_options["[yamlform-submission:values:$key:raw]"] = $title;
      }
      $text_element_options["[yamlform-submission:values:$key:value]"] = $title;
    }

    $default_optgroup = (string) $this->t('Default');
    $elements_optgroup = (string) $this->t('Elements');

    // Disable client-side HTML5 validation which is having issues with hidden
    // element validation.
    // @see http://stackoverflow.com/questions/22148080/an-invalid-form-control-with-name-is-not-focusable
    $form['#attributes']['novalidate'] = 'novalidate';

    // To.
    $form['to'] = [
      '#type' => 'details',
      '#title' => $this->t('Send to'),
      '#open' => TRUE,
    ];
    $form['to']['to_mail'] = [
      '#type' => 'yamlform_select_other',
      '#title' => $this->t('To email'),
      '#options' => [
        YamlFormSelectOther::OTHER_OPTION => $this->t('Custom to email address...'),
        $default_optgroup => ['default' => $this->getDefaultConfigurationValue('to_mail')],
        $elements_optgroup => $mail_element_options,
      ],
      '#other__placeholder' => $this->t('Enter to email address...'),
      '#other__type' => 'yamlform_email_multiple',
      '#other__allow_tokens' => TRUE,
      '#required' => TRUE,
      '#parents' => ['settings', 'to_mail'],
      '#default_value' => $this->configuration['to_mail'],
    ];
    $form['to']['cc_mail'] = [
      '#type' => 'yamlform_select_other',
      '#title' => $this->t('CC email'),
      '#options' => [
        '' => '',
        YamlFormSelectOther::OTHER_OPTION => $this->t('Custom CC email address...'),
        $default_optgroup => ['default' => $this->getDefaultConfigurationValue('cc_mail')],
        $elements_optgroup => $mail_element_options,
      ],
      '#other__placeholder' => $this->t('Enter CC email address...'),
      '#other__type' => 'yamlform_email_multiple',
      '#parents' => ['settings', 'cc_mail'],
      '#other__allow_tokens' => TRUE,
      '#default_value' => $this->configuration['cc_mail'],
    ];
    $form['to']['bcc_mail'] = [
      '#type' => 'yamlform_select_other',
      '#title' => $this->t('BCC email'),
      '#options' => [
        '' => '',
        YamlFormSelectOther::OTHER_OPTION => $this->t('Custom BCC email address...'),
        $default_optgroup => ['default' => $this->getDefaultConfigurationValue('bcc_mail')],
        $elements_optgroup => $mail_element_options,
      ],
      '#other__placeholder' => $this->t('Enter BCC email address...'),
      '#other__type' => 'yamlform_email_multiple',
      '#other__allow_tokens' => TRUE,
      '#parents' => ['settings', 'bcc_mail'],
      '#default_value' => $this->configuration['bcc_mail'],
    ];

    // From.
    $form['from'] = [
      '#type' => 'details',
      '#title' => $this->t('Send from'),
      '#open' => TRUE,
    ];
    $form['from']['from_mail'] = [
      '#type' => 'yamlform_select_other',
      '#title' => $this->t('From email'),
      '#options' => [
        YamlFormSelectOther::OTHER_OPTION => $this->t('Custom from email address...'),
        $default_optgroup => ['default' => $this->getDefaultConfigurationValue('from_mail')],
        $elements_optgroup => $mail_element_options,
      ],
      '#other__placeholder' => $this->t('Enter from email address...'),
      '#other__type' => 'yamlform_email_multiple',
      '#other__allow_tokens' => TRUE,
      '#required' => TRUE,
      '#parents' => ['settings', 'from_mail'],
      '#default_value' => $this->configuration['from_mail'],
    ];
    $form['from']['from_name'] = [
      '#type' => 'yamlform_select_other',
      '#title' => $this->t('From name'),
      '#options' => [
        '' => '',
        YamlFormSelectOther::OTHER_OPTION => $this->t('Custom from name...'),
        $default_optgroup => ['default' => $this->getDefaultConfigurationValue('from_name')],
        $elements_optgroup => $text_element_options,
      ],
      '#other__placeholder' => $this->t('Enter from name...'),
      '#parents' => ['settings', 'from_name'],
      '#default_value' => $this->configuration['from_name'],
    ];

    // Message.
    $form['message'] = [
      '#type' => 'details',
      '#title' => $this->t('Message'),
      '#open' => TRUE,
    ];
    $form['message']['subject'] = [
      '#type' => 'yamlform_select_other',
      '#title' => $this->t('Subject'),
      '#options' => [
        YamlFormSelectOther::OTHER_OPTION => $this->t('Custom subject...'),
        $default_optgroup => ['default' => $this->getDefaultConfigurationValue('subject')],
        $elements_optgroup => $text_element_options,
      ],
      '#other__placeholder' => $this->t('Enter subject...'),
      '#required' => TRUE,
      '#parents' => ['settings', 'subject'],
      '#default_value' => $this->configuration['subject'],
    ];

    // Body.
    $form['message']['body'] = [
      '#type' => 'yamlform_select_other',
      '#title' => $this->t('Body'),
      '#options' => [
        YamlFormSelectOther::OTHER_OPTION => $this->t('Custom body...'),
        'default' => $this->t('Default'),
        $elements_optgroup => $text_element_options,
      ],
      '#other__type' => 'yamlform_codemirror',
      '#other__mode' => 'html',
      '#required' => TRUE,
      '#parents' => ['settings', 'body'],
      '#default_value' => $this->configuration['body'],
    ];
    $body_default_values = $this->getBodyDefaultValues();
    foreach ($body_default_values as $format => $default_value) {
      $form['message']['body_default_' . $format] = [
        '#type' => 'yamlform_codemirror',
        '#mode' => $format,
        '#title' => $this->t('Body default value (@format)', ['@label' => $format]),
        '#title_display' => 'hidden',
        '#default_value' => $default_value,
        '#attributes' => ['readonly' => 'readonly', 'disabled' => 'disabled'],
        '#states' => [
          'visible' => [
            ':input[name="settings[body][select]"]' => ['value' => 'default'],
            ':input[name="settings[html]"]' => ['checked' => ($format == 'html') ? TRUE : FALSE],
          ],
        ],
      ];
    }
    $form['message']['token_tree_link'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => [
        'yamlform',
        'yamlform-submission',
      ],
      '#click_insert' => FALSE,
      '#dialog' => TRUE,
    ];

    // Elements.
    $form['elements'] = [
      '#type' => 'details',
      '#title' => $this->t('Included email values'),
      '#open' => $this->configuration['excluded_elements'] ? TRUE : FALSE,
    ];
    $form['elements']['excluded_elements'] = [
      '#type' => 'yamlform_excluded_elements',
      '#description' => $this->t('The selected elements will be included in the [yamlform-submission:values] token. Individual values may still be printed if explicitly specified as a [yamlform-submission:values:?] in the email body template.'),
      '#yamlform' => $this->yamlform,
      '#default_value' => $this->configuration['excluded_elements'],
      '#parents' => ['settings', 'excluded_elements'],
    ];

    // Settings.
    $form['settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Settings'),
    ];
    $form['settings']['html'] = [
      '#type' => 'checkbox',
      '#title' => t('Send email as HTML'),
      '#return_value' => TRUE,
      '#access' => $this->supportsHtml(),
      '#parents' => ['settings', 'html'],
      '#default_value' => $this->configuration['html'],
    ];
    $form['settings']['attachments'] = [
      '#type' => 'checkbox',
      '#title' => t('Include files as attachments'),
      '#return_value' => TRUE,
      '#access' => $this->supportsAttachments(),
      '#parents' => ['settings', 'attachments'],
      '#default_value' => $this->configuration['attachments'],
    ];
    $form['settings']['debug'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable debugging'),
      '#description' => $this->t('If checked sent emails will be displayed onscreen to all users.'),
      '#return_value' => TRUE,
      '#parents' => ['settings', 'debug'],
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
    foreach ($this->configuration as $name => $value) {
      if (isset($values[$name])) {
        $this->configuration[$name] = $values[$name];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(YamlFormSubmissionInterface $yamlform_submission, $update = TRUE) {
    $is_results_disabled = $yamlform_submission->getYamlForm()->getSetting('results_disabled');
    $is_completed = ($yamlform_submission->getState() == YamlFormSubmissionInterface::STATE_COMPLETED);
    if ($is_results_disabled || $is_completed) {
      $message = $this->getMessage($yamlform_submission);
      $this->sendMessage($message);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getMessage(YamlFormSubmissionInterface $yamlform_submission) {
    $token_data = [
      'yamlform' => $yamlform_submission->getYamlForm(),
      'yamlform-submission' => $yamlform_submission,
      'yamlform-submission-options' => [
        'email' => TRUE,
        'excluded_elements' => $this->configuration['excluded_elements'],
        'html' => ($this->configuration['html'] && $this->supportsHtml()),
      ],
    ];
    $token_options = ['clear' => TRUE];

    $message = $this->configuration;
    unset($message['excluded_elements']);

    // Replace 'default' values and [tokens] with configuration default values.
    foreach ($message as $key => $value) {
      if ($value === 'default') {
        $message[$key] = $this->getDefaultConfigurationValue($key);
      }
      $message[$key] = $this->token->replace($message[$key], $token_data, $token_options);
    }

    // Trim the message body.
    $message['body'] = trim($message['body']);

    // Alter body based on the mail system sender.
    if ($this->configuration['html'] && $this->supportsHtml()) {
      switch ($this->getMailSystemSender()) {
        case 'swiftmailer':
          // SwiftMailer requires that the body be valid Markup.
          $message['body'] = Markup::create($message['body']);
          break;
      }
    }
    else {
      // Since Drupal might be rendering a token into the body as markup
      // we need to decode all HTML entities which are being sent as plain text.
      $message['body'] = html_entity_decode($message['body']);
    }

    // Add attachments.
    if ($this->configuration['attachments'] && $this->supportsAttachments()) {
      $message['attachments'] = [];
      $elements = $this->yamlform->getElementsInitializedAndFlattened();
      foreach ($elements as $key => $element) {
        if (!isset($element['#type']) || $element['#type'] != 'managed_file') {
          continue;
        }
        $fid = $yamlform_submission->getData($key);
        if (!$fid) {
          continue;
        }
        /** @var \Drupal\file\FileInterface $file */
        if ($file = File::load($fid)) {
          $filepath = \Drupal::service('file_system')->realpath($file->getFileUri());
          $message['attachments'][] = [
            'filecontent' => file_get_contents($filepath),
            'filename' => $file->getFilename(),
            'filemime' => $file->getMimeType(),
            // Add URL to be used by resend form.
            'file' => $file,
          ];
        }
      }
    }

    // Add form submission.
    $message['yamlform_submission'] = $yamlform_submission;

    return $message;
  }

  /**
   * {@inheritdoc}
   */
  public function sendMessage(array $message) {
    // Send mail.
    $to = $message['to_mail'];
    $from = $message['from_mail'] . (($message['from_name']) ? ' <' . $message['from_name'] . '>' : '');
    $current_langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $this->mailManager->mail('yamlform', 'email.' . $this->getHandlerId(), $to, $current_langcode, $message, $from);

    // Log message.
    $context = [
      '@form' => $this->getYamlForm()->label(),
      '@title' => $this->label(),
    ];
    \Drupal::logger('yamlform.email')->notice('@form form sent @title email.', $context);

    // Debug by displaying send email onscreen.
    if ($this->configuration['debug']) {
      $t_args = [
        '%from_name' => $message['from_name'],
        '%from_mail' => $message['from_mail'],
        '%to_mail' => $message['to_mail'],
        '%subject' => $message['subject'],
      ];
      $build = [];
      $build['message'] = [
        '#markup' => $this->t('%subject sent to %to_mail from %from_name [%from_mail].', $t_args),
        '#prefix' => '<b>',
        '#suffix' => '</b>',
      ];
      if ($message['html']) {
        $build['body'] = [
          '#markup' => $message['body'],
          '#allowed_tags' => Xss::getAdminTagList(),
          '#prefix' => '<div>',
          '#suffix' => '</div>',
        ];
      }
      else {
        $build['body'] = [
          '#markup' => $message['body'],
          '#prefix' => '<pre>',
          '#suffix' => '</pre>',
        ];
      }
      drupal_set_message(\Drupal::service('renderer')->render($build), 'warning');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function resendMessageForm(array $message) {
    $element = [];
    $element['to_mail'] = [
      '#type' => 'yamlform_email_multiple',
      '#title' => $this->t('To email'),
      '#default_value' => $message['to_mail'],
    ];
    $element['from_mail'] = [
      '#type' => 'yamlform_email_multiple',
      '#title' => $this->t('From email'),
      '#required' => TRUE,
      '#default_value' => $message['from_mail'],
    ];
    $element['from_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('From name'),
      '#required' => TRUE,
      '#default_value' => $message['from_name'],
    ];
    $element['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#default_value' => $message['subject'],
    ];
    $body_format = ($this->configuration['html']) ? 'html' : 'text';
    $element['body'] = [
      '#type' => 'yamlform_codemirror',
      '#mode' => $body_format,
      '#title' => $this->t('Message (@format)', ['@format' => ($this->configuration['html']) ? $this->t('HTML') : $this->t('Plain text')]),
      '#rows' => 10,
      '#required' => TRUE,
      '#default_value' => $message['body'],
    ];
    $element['html'] = [
      '#type' => 'value',
      '#value' => $message['html'],
    ];
    $element['attachments'] = [
      '#type' => 'value',
      '#value' => $message['attachments'],
    ];

    // Display attached files.
    if ($message['attachments']) {
      $file_links = [];
      foreach ($message['attachments'] as $attachment) {
        $file_links[] = [
          '#theme' => 'file_link',
          '#file' => $attachment['file'],
          '#prefix' => '<div>',
          '#suffix' => '</div>',
        ];
      }
      $element['files'] = [
        '#type' => 'item',
        '#title' => $this->t('Attachments'),
        '#markup' => \Drupal::service('renderer')->render($file_links),
      ];
    }

    return $element;
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
   * Check that HTML emails are supported.
   *
   * @return bool
   *   TRUE if HTML email is supported.
   */
  protected function supportsHtml() {
    return TRUE;
  }

  /**
   * Check that emailing files as attachments is supported.
   *
   * @return bool
   *   TRUE if emailing files as attachments is supported.
   */
  protected function supportsAttachments() {
    // If 'system.mail.interface.default' is 'test_mail_collector' allow
    // email attachments during testing.
    if (\Drupal::configFactory()->get('system.mail')->get('interface.default') == 'test_mail_collector') {
      return TRUE;
    }

    return \Drupal::moduleHandler()->moduleExists('mailsystem');
  }

  /**
   * Get the Mail System's sender module name.
   *
   * @return string
   *   The Mail System's sender module name.
   */
  protected function getMailSystemSender() {
    $mailsystem_config = $this->configFactory->get('mailsystem.settings');
    $mailsystem_sender = $mailsystem_config->get('yamlform.sender') ?: $mailsystem_config->get('defaults.sender');
    return $mailsystem_sender;
  }

  /**
   * Get message body default values, which can be formatted as text or html.
   *
   * @param string $format
   *   If a format (text or html) is provided the default value for the
   *   specified format is return. If no format is specified an associative
   *   array containing the text and html default body values will be returned.
   *
   * @return string|array
   *   A single (text or html) default body value or an associative array
   *   containing both the text and html default body values.
   */
  protected function getBodyDefaultValues($format = NULL) {
    $yamlform_settings = $this->configFactory->get('yamlform.settings');
    $formats = [
      'text' => $yamlform_settings->get('mail.default_body_text') ?: '[yamlform-submission:values]',
      'html' => $yamlform_settings->get('mail.default_body_html') ?: '[yamlform-submission:values]',
    ];
    return ($format === NULL) ? $formats : $formats[$format];
  }

}
