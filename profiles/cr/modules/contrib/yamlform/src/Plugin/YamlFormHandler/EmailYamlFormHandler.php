<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormHandler\EmailYamlFormHandler.
 */

namespace Drupal\yamlform\Plugin\YamlFormHandler;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;
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
 *   id = "email",
 *   label = @Translation("Email"),
 *   description = @Translation("Sends a YAML form submission via an email."),
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
   * @var \Drupal\Core\Utility\Token $token
   */
  protected $token;

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
      'from_mail' => 'default',
      'from_name' => 'default',
      'subject' => 'default',
      'body' => 'default',
      'excluded_inputs' => [],
      'html' => FALSE,
      'attachments' => FALSE,
      'debug' => FALSE,
    ];
  }

  /**
   * Get mail configuration values.
   *
   * @return array
   *   An associative array containing email configuration values.
   */
  protected function getEmailConfiguration() {
    $configuration = $this->getConfiguration();
    $settings = $this->getConfigurationSettings();
    $email = [];
    foreach ($configuration['settings'] as $key => $value) {
      if ($value === 'default') {
        $email[$key] = $settings[$key]['default'];
      }
      else {
        $email[$key] = $value;
      }
    }
    return $email;
  }

  /**
   * Get configuration settings including type, label, and default value.
   *
   * @return array
   *   An associative array keyed by configuration name containing each
   *   configuration setting's type, label, and default value.
   */
  protected function getConfigurationSettings() {
    $yamlform_settings = $this->configFactory->get('yamlform.settings');
    $site_settings = $this->configFactory->get('system.site');
    $body_format = ($this->configuration['html']) ? 'html' : 'text';
    return [
      'to_mail' => [
        'type' => 'textfield',
        'label' => $this->t('Email to address'),
        'default' => $yamlform_settings->get('mail.default_to_mail') ?: $site_settings->get('mail') ?: ini_get('sendmail_from'),
      ],
      'from_mail' => [
        'type' => 'textfield',
        'label' => $this->t('Email from address'),
        'default' => $yamlform_settings->get('mail.default_from_mail') ?: $site_settings->get('mail') ?: ini_get('sendmail_from'),
      ],
      'from_name' => [
        'type' => 'textfield',
        'label' => $this->t('Email from name'),
        'default' => $yamlform_settings->get('mail.default_from_name') ?: $site_settings->get('name'),
      ],
      'subject' => [
        'type' => 'textfield',
        'label' => $this->t('Email subject'),
        'default' => $yamlform_settings->get('mail.default_subject') ?: 'Form submission from: [yamlform-submission:source-entity]',
      ],
      'body' => [
        'type' => 'yamlform_codemirror_text',
        'label' => $this->t('Email body'),
        'default' => $this->getBodyDefaultValues($body_format),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $settings = $this->getConfigurationSettings();

    $form['to'] = [
      '#type' => 'details',
      '#title' => $this->t('Send to'),
      '#open' => TRUE,
    ];
    $form['from'] = [
      '#type' => 'details',
      '#title' => $this->t('Send from'),
      '#open' => TRUE,
    ];
    $form['message'] = [
      '#type' => 'details',
      '#title' => $this->t('Message'),
      '#open' => TRUE,
    ];

    $mail_input_options = [];
    $text_input_options = [];
    $inputs = $this->yamlform->getFlattenedInputs();
    foreach ($inputs as $key => $input) {
      $title = (isset($input['#title'])) ? new FormattableMarkup('@title (@key)', ['@title' => $input['#title'], '@key' => $key]) : $key;
      // Note: Token must use the raw :value for the element.
      $token = "[yamlform-submission:values:$key:value]";
      if (isset($input['#type']) && in_array($input['#type'], ['email', 'hidden', 'value', 'selected', 'radios'])) {
        $mail_input_options[$token] = $title;
      }
      $text_input_options[$token] = $title;
    }

    foreach ($settings as $config_name => $config_settings) {
      $type = $config_settings['type'];
      $label = $config_settings['label'];
      $group = preg_match('/^(to|from)/', $config_name, $match) ? $match[0] : 'message';

      $inputs_optgroup = (string) $this->t('Inputs');

      // Set options.
      $options = [];
      if ($type == 'textarea' || strpos($type, 'yamlform_codemirror_') === 0) {
        $options['default'] = $this->t('Default:');
      }
      else {
        $options['default'] = $this->t('Default: @default', ['@default' => $config_settings['default']]);
      }
      $options['custom'] = $this->t('Custom...');

      if ($type == 'email') {
        if ($mail_input_options) {
          $options[$inputs_optgroup] = $mail_input_options;
        }
        $custom_label = $this->t('Enter email address...');
      }
      else {
        if ($text_input_options) {
          $options[$inputs_optgroup] = $text_input_options;
        }
        $custom_label = $this->t('Enter text...');
      }

      $value = $this->configuration[$config_name];
      if (in_array($value, ['default', 'custom']) || isset($options[$inputs_optgroup][$value])) {
        $custom_value = '';
      }
      else {
        $custom_value = $value;
        $value = 'custom';
      }

      $form[$group][$config_name] = [
        '#type' => 'select',
        '#title' => $label,
        '#options' => $options,
        '#required' => TRUE,
        '#default_value' => $value,
      ];
      $form[$group][$config_name . '_custom'] = [
        '#type' => $type,
        '#title' => $this->t('@label custom', ['@label' => $label]),
        '#title_display' => 'hidden',
        '#default_value' => $custom_value,
        '#attributes' => ['placeholder' => $custom_label],
        '#states' => [
          'visible' => [
            ':input[name="settings[' . $group . '][' . $config_name . ']"]' => ['value' => 'custom'],
          ],
          'required' => [
            ':input[name="settings[' . $group . '][' . $config_name . ']"]' => ['value' => 'custom'],
          ],
        ],
      ];
    }

    // Display 'default' body value with selected format (text or html)
    // depending on the user's selection.
    $body_default_values = $this->getBodyDefaultValues();
    foreach ($body_default_values as $format => $default_value) {
      $form['message']['body_default_' . $format] = [
        '#type' => 'yamlform_codemirror_' . $format,
        '#title' => $this->t('Body default value (@format)', ['@label' => $format]),
        '#title_display' => 'hidden',
        '#default_value' => $default_value,
        '#attributes' => ['readonly' => 'readonly', 'disabled' => 'disabled'],
        '#states' => [
          'visible' => [
            ':input[name="settings[message][body]"]' => ['value' => 'default'],
            ':input[name="settings[settings][html]"]' => ['checked' => ($format == 'html') ? TRUE : FALSE],
          ],
        ],
      ];
    }

    // Inputs.
    $form['inputs'] = [
      '#type' => 'details',
      '#title' => $this->t('Included email values'),
      '#open' => $this->configuration['excluded_inputs'] ? TRUE : FALSE,
    ];
    $form['inputs']['excluded_inputs'] = [
      '#type' => 'yamlform_excluded_inputs',
      '#description' => $this->t('The selected inputs will be included in the [yamlform-submission:values] token. Individual values may still be printed if explicitly specified as a [yamlform-submission:values:?] in the email body template.'),
      '#yamlform' => $this->yamlform,
      '#default_value' => $this->configuration['excluded_inputs'],
    ];

    // Token.
    $form['message']['token_tree_link'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => [
        'yamlform',
        'yamlform-submission',
      ],
      '#dialog' => TRUE,
    ];

    // Settings.
    $form['settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Settings'),
      '#open' => TRUE,
    ];
    $form['settings']['html'] = [
      '#type' => 'checkbox',
      '#title' => t('Send email as HTML'),
      '#default_value' => $this->configuration['html'],
      '#access' => $this->supportsHtml(),
    ];

    $form['settings']['attachments'] = [
      '#type' => 'checkbox',
      '#title' => t('Include files as attachments'),
      '#default_value' => $this->configuration['attachments'],
      '#access' => $this->supportsAttachments(),
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

    // Get email settings.
    $values = $form_state->getValue('to') + $form_state->getValue('from') + $form_state->getValue('message');
    foreach (['to_mail', 'from_mail', 'from_name', 'subject', 'body'] as $key) {
      $value = $values[$key];
      if ($value == 'custom') {
        $value = $values[$key . '_custom'];
      }
      $this->configuration[$key] = $value;
    }

    // Get other settings.
    $values = $form_state->getValues();
    $this->configuration['excluded_inputs'] = $values['inputs']['excluded_inputs'];
    $this->configuration['html'] = $values['settings']['html'];
    $this->configuration['attachments'] = $values['settings']['attachments'];
    $this->configuration['debug'] = $values['debug']['debug'];
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

  /**
   * {@inheritdoc}
   */
  public function getMessage(YamlFormSubmissionInterface $yamlform_submission) {
    $token_data = [
      'yamlform' => $yamlform_submission->getYamlForm(),
      'yamlform-submission' => $yamlform_submission,
      'yamlform-submission-options' => [
        'email' => TRUE,
        'excluded_inputs' => $this->configuration['excluded_inputs'],
        'html' => ($this->configuration['html'] && $this->supportsHtml()),
      ],
    ];

    $message = $this->configuration;
    unset($message['excluded_inputs']);

    // Replace 'default' values and [tokens] with settings default.
    $settings = $this->getConfigurationSettings();
    foreach ($settings as $setting_key => $setting) {
      if (empty($message[$setting_key]) || $message[$setting_key] == 'default') {
        $message[$setting_key] = $setting['default'];
      }
      $message[$setting_key] = $this->token->replace($message[$setting_key], $token_data);
    }

    // Trim the message body.
    $message['body'] = trim($message['body']);

    if ($this->configuration['html'] && $this->supportsHtml()) {
      switch ($this->getMailSystemSender()) {
        case 'swiftmailer':
          // SwiftMailer requires that the body be valid Markup.
          $message['body'] = Markup::create($message['body']);
          break;
      }
    }

    // Add attachments.
    if ($this->configuration['attachments'] && $this->supportsAttachments()) {
      $message['attachments'] = [];
      $inputs = $this->yamlform->getFlattenedInputs();
      foreach ($inputs as $key => $input) {
        if (!isset($input['#type']) || $input['#type'] != 'managed_file') {
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

    return $message;
  }

  /**
   * {@inheritdoc}
   */
  public function sendMessage(array $message) {
    // Send mail.
    $to = $message['to_mail'];
    $from = $message['from_mail'] . ' <' . $message['from_name'] . '>';
    $current_langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $this->mailManager->mail('yamlform', 'email.' . $this->getHandlerId(), $to, $current_langcode, $message, $from);

    // Log message.
    $variables = [
      '@from_name' => $message['from_name'],
      '@from_mail' => $message['from_mail'],
      '@to_mail' => $message['to_mail'],
      '@subject' => $message['subject'],
    ];
    \Drupal::logger('yamlform.email')->notice('@subject sent to @to_mail from @from_name [@from_mail].', $variables);

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
      '#type' => 'email',
      '#title' => $this->t('To email'),
      '#default_value' => $message['to_mail'],
    ];
    $element['from_mail'] = [
      '#type' => 'email',
      '#title' => $this->t('From email '),
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
      '#type' => 'yamlform_codemirror_' . $body_format,
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
