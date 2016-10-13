<?php

namespace Drupal\yamlform\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormInterface;
use Drupal\yamlform\YamlFormRequestInterface;
use Drupal\yamlform\YamlFormSubmissionStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base form for deleting form submission.
 */
abstract class YamlFormSubmissionsDeleteFormBase extends ConfirmFormBase {

  /**
   * Default number of submission to be deleted during batch processing.
   *
   * @var int
   */
  protected $batchLimit = 1000;

  /**
   * The form entity.
   *
   * @var \Drupal\yamlform\YamlFormInterface
   */
  protected $yamlform;

  /**
   * The form source entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $sourceEntity;

  /**
   * The form submission storage.
   *
   * @var \Drupal\yamlform\YamlFormSubmissionStorageInterface
   */
  protected $submissionStorage;

  /**
   * Form request handler.
   *
   * @var \Drupal\yamlform\YamlFormRequestInterface
   */
  protected $requestHandler;

  /**
   * Constructs a new YamlFormResultsDeleteBaseForm object.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionStorageInterface $yamlform_submission_storage
   *   The form submission storage.
   * @param \Drupal\yamlform\YamlFormRequestInterface $request_handler
   *   The form request handler.
   */
  public function __construct(YamlFormSubmissionStorageInterface $yamlform_submission_storage, YamlFormRequestInterface $request_handler) {
    $this->submissionStorage = $yamlform_submission_storage;
    $this->requestHandler = $request_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')->getStorage('yamlform_submission'),
      $container->get('yamlform.request')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Clear');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    list($this->yamlform, $this->sourceEntity) = $this->requestHandler->getYamlFormEntities();
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirectUrl($this->getCancelUrl());
    if ($this->submissionStorage->getTotal($this->yamlform, $this->sourceEntity) < $this->getBatchLimit()) {
      $this->submissionStorage->deleteAll($this->yamlform, $this->sourceEntity);
      drupal_set_message($this->getFinishedMessage());
    }
    else {
      $this->batchSet($this->yamlform, $this->sourceEntity);
    }
  }

  /**
   * Message to displayed after submissions are deleted.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   Message to be displayed after delete has finished.
   */
  public function getFinishedMessage() {
    return $this->t('Form submissions cleared.');
  }

  /**
   * Batch API; Initialize batch operations.
   *
   * @param \Drupal\yamlform\YamlFormInterface|null $yamlform
   *   The form.
   * @param \Drupal\Core\Entity\EntityInterface|null $entity
   *   The form's source entity.
   */
  public function batchSet(YamlFormInterface $yamlform = NULL, EntityInterface $entity = NULL) {
    $parameters = [
      $yamlform,
      $entity,
      $this->submissionStorage->getMaxSubmissionId($yamlform, $entity),
    ];
    $batch = [
      'title' => $this->t('Clear submissions'),
      'init_message' => $this->t('Clearing submission data'),
      'error_message' => $this->t('The submissions could not be cleared because an error occurred.'),
      'operations' => [
        [[$this, 'batchProcess'], $parameters],
      ],
      'finished' => [$this, 'batchFinish'],
    ];

    batch_set($batch);
  }

  /**
   * Get the number of submissions to be deleted with each batch.
   *
   * @return int
   *   Number of submissions to be deleted with each batch.
   */
  public function getBatchLimit() {
    return $this->config('yamlform.settings')->get('batch.default_batch_delete_size') ?: $this->batchLimit;
  }

  /**
   * Batch API callback; Delete submissions.
   *
   * @param \Drupal\yamlform\YamlFormInterface|null $yamlform
   *   The form.
   * @param \Drupal\Core\Entity\EntityInterface|null $entity
   *   The form's source entity.
   * @param int $max_sid
   *   The max submission ID to be delete.
   * @param mixed|array $context
   *   The batch current context.
   */
  public function batchProcess(YamlFormInterface $yamlform = NULL, EntityInterface $entity = NULL, $max_sid, &$context) {
    // ISSUE:
    // $this->submissionStorage is not being setup via
    // YamlFormSubmissionsDeleteFormBase::__construct.
    //
    // WORKAROUND:
    // Reset it for each batch process.
    $this->submissionStorage = \Drupal::entityTypeManager()->getStorage('yamlform_submission');

    if (empty($context['sandbox'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = $this->submissionStorage->getTotal($yamlform, $entity);
      $context['results']['yamlform'] = $yamlform;
      $context['results']['entity'] = $entity;
    }

    // Track progress.
    $context['sandbox']['progress'] += $this->submissionStorage->deleteAll($yamlform, $entity, $this->getBatchLimit(), $max_sid);

    $context['message'] = $this->t('Deleting @count of @total submissions...', ['@count' => $context['sandbox']['progress'], '@total' => $context['sandbox']['max']]);

    // Track finished.
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  /**
   * Batch API callback; Completed deletion.
   *
   * @param bool $success
   *   TRUE if batch successfully completed.
   * @param array $results
   *   Batch results.
   * @param array $operations
   *   An array of function calls (not used in this function).
   */
  public function batchFinish($success = FALSE, array $results, array $operations) {
    if (!$success) {
      drupal_set_message($this->t('Finished with an error.'));
    }
    else {
      drupal_set_message($this->getFinishedMessage());
    }
  }

}
