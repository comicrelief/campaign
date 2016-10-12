<?php

namespace Drupal\yamlform;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\Token;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Psr\Log\LoggerInterface;

/**
 * Defines the form message (and login) manager.
 */
class YamlFormMessageManager implements YamlFormMessageManagerInterface {

  use StringTranslationTrait;

  /**
   * The configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Form submission storage.
   *
   * @var \Drupal\yamlform\YamlFormSubmissionStorageInterface
   */
  protected $entityStorage;

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Form request handler.
   *
   * @var \Drupal\yamlform\YamlFormRequestInterface
   */
  protected $requestHandler;

  /**
   * A form.
   *
   * @var \Drupal\yamlform\YamlFormInterface
   */
  protected $yamlform;

  /**
   * The source entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $sourceEntity;

  /**
   * A form submission.
   *
   * @var \Drupal\yamlform\YamlFormSubmissionInterface
   */
  protected $yamlformSubmission;

  /**
   * Constructs a YamlFormMessageManager object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration object factory.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\yamlform\YamlFormRequestInterface $request_handler
   *   The form request handler.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityManagerInterface $entity_manager, Token $token, LoggerInterface $logger, YamlFormRequestInterface $request_handler) {
    $this->configFactory = $config_factory;
    $this->entityStorage = $entity_manager->getStorage('yamlform_submission');
    $this->token = $token;
    $this->logger = $logger;
    $this->requestHandler = $request_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function setYamlForm(YamlFormInterface $yamlform = NULL) {
    $this->yamlform = $yamlform;
  }

  /**
   * {@inheritdoc}
   */
  public function setSourceEntity(EntityInterface $entity = NULL) {
    $this->sourceEntity = $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setYamlFormSubmission(YamlFormSubmissionInterface $yamlform_submission = NULL) {
    $this->yamlformSubmission = $yamlform_submission;
    if ($yamlform_submission && empty($this->yamlform)) {
      $this->yamlform = $yamlform_submission->getYamlForm();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function display($key, $type = 'status') {
    if ($build = $this->build($key)) {
      drupal_set_message(\Drupal::service('renderer')->renderPlain($build), $type);
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build($key) {
    if ($message = $this->get($key)) {
      return [
        '#markup' => $message,
        '#allowed_tags' => Xss::getAdminTagList(),
      ];
    }
    else {
      return [];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function get($key) {
    $yamlform_settings = ($this->yamlform) ? $this->yamlform->getSettings() : [];
    if (!empty($yamlform_settings[$key])) {
      return $this->replaceTokens($yamlform_settings[$key]);
    }

    $default_settings = $this->configFactory->get('yamlform.settings')->get('settings');
    if (!empty($default_settings['default_' . $key])) {
      return $this->replaceTokens($default_settings['default_' . $key]);
    }

    $yamlform = $this->yamlform;
    $source_entity = $this->sourceEntity;

    $t_args = [
      '%form' => ($source_entity) ? $source_entity->label() : $yamlform->label(),
      ':handlers_href' => $yamlform->toUrl('handlers-form')->toString(),
      ':settings_href' => $yamlform->toUrl('settings-form')->toString(),
      ':duplicate_href' => $yamlform->toUrl('duplicate-form')->toString(),
    ];

    switch ($key) {
      case YamlFormMessageManagerInterface::ADMIN_ACCESS:
        return $this->t('This form is <a href=":settings_href">closed</a>. Only submission administrators are allowed to access this form and create new submissions.', $t_args);

      case YamlFormMessageManagerInterface::SUBMISSION_DEFAULT_CONFIRMATION:
        return $this->t('New submission added to %form.', $t_args);

      case YamlFormMessageManagerInterface::FORM_SAVE_EXCEPTION:
        return $this->t('This form is currently not saving any submitted data. Please enable the <a href=":settings_href">saving of results</a> or add a <a href=":handlers_href">submission handler</a> to the form.', $t_args);

      case YamlFormMessageManagerInterface::SUBMISSION_PREVIOUS:
        $yamlform_submission = $this->entityStorage->getLastSubmission($yamlform, $source_entity);
        $submission_route_name = $this->requestHandler->getRouteName($yamlform_submission, $source_entity, 'yamlform.user.submission');
        $submission_route_parameters = $this->requestHandler->getRouteParameters($yamlform_submission, $source_entity);
        $t_args[':submission_href'] = Url::fromRoute($submission_route_name, $submission_route_parameters)->toString();

        return $this->t('You have already submitted this form.') . ' ' . $this->t('<a href=":submission_href">View your previous submission</a>.', $t_args);

      case YamlFormMessageManagerInterface::SUBMISSIONS_PREVIOUS:
        $submissions_route_name = $this->requestHandler->getRouteName($yamlform, $source_entity, 'yamlform.user.submissions');
        $submissions_route_parameters = $this->requestHandler->getRouteParameters($yamlform, $source_entity);
        $t_args[':submissions_href'] = Url::fromRoute($submissions_route_name, $submissions_route_parameters)->toString();

        return $this->t('You have already submitted this form.') . ' ' . $this->t('<a href=":submissions_href">View your previous submissions</a>.', $t_args);

      case YamlFormMessageManagerInterface::SUBMISSION_UPDATED:
        return $this->t('Submission updated in %form.', $t_args);

      case YamlFormMessageManagerInterface::SUBMISSION_TEST;
        return $this->t("The below form has been prepopulated with custom/random test data. When submitted, this information <strong>will still be saved</strong> and/or <strong>sent to designated recipients</strong>.", $t_args);

      case YamlFormMessageManagerInterface::TEMPLATE_PREVIEW;
        return $this->t('You are previewing the below template, which can be used to <a href=":duplicate_href">create a new form</a>. <strong>Submitted data will be ignored</strong>.', $t_args);

      default:
        return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function log($key, $type = 'warning') {
    $yamlform = $this->yamlform;
    $context = [
      'link' => $yamlform->toLink($this->t('Edit'), 'edit-form')->toString(),
    ];

    switch ($key) {
      case YamlFormMessageManagerInterface::FORM_FILE_UPLOAD_EXCEPTION:
        $message = 'To support file uploads the saving of submission must be enabled. <strong>All uploaded load files would be lost</strong> Please either uncheck \'Disable saving of submissions\' or remove all the file upload elements.';
        break;

      case YamlFormMessageManagerInterface::FORM_SAVE_EXCEPTION:
        $context['%form'] = $yamlform->label();
        $message = '%form is not saving any submitted data and has been disabled.';
        break;
    }

    $this->logger->$type($message, $context);
  }

  /**
   * Replace tokens in text.
   *
   * @param string $text
   *   A string of text that main contain tokens.
   *
   * @return string
   *   Text will tokens replaced.
   */
  protected function replaceTokens($text) {
    // Most strings won't contain tokens so lets check and return ASAP.
    if (!is_string($text) || strpos($text, '[') === FALSE) {
      return $text;
    }

    $token_data = [
      'yamlform' => $this->yamlform,
      'yamlform-submission' => $this->yamlformSubmission,
    ];
    $token_options = ['clear' => TRUE];

    return $this->token->replace($text, $token_data, $token_options);
  }

}
