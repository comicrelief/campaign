<?php

/**
 * @file
 * Contains Drupal\yamlform\Form\YamlFormResultsClearForm.
 */

namespace Drupal\yamlform\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormInterface;
use Drupal\yamlform\YamlFormSubmissionStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base form for deleting YAML form submission.
 */
abstract class YamlFormSubmissionsDeleteFormBase extends ConfirmFormBase {

  /**
   * Number of submission to be deleted during batch processing.
   *
   * @var int
   */
  protected $batchLimit = 1000;

  /**
   * The YAML form entity.
   *
   * @var \Drupal\yamlform\Entity\YamlForm
   */
  protected $yamlform;

  /**
   * The YAML form submission storage.
   *
   * @var \Drupal\yamlform\YamlFormSubmissionStorageInterface
   */
  protected $submissionStorage;

  /**
   * Constructs a new YamlFormResultsDeleteBaseForm object.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionStorageInterface $yamlform_submission_storage
   *   The YAML form submission storage.
   */
  public function __construct(YamlFormSubmissionStorageInterface $yamlform_submission_storage) {
    $this->submissionStorage = $yamlform_submission_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')->getStorage('yamlform_submission')
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
  public function buildForm(array $form, FormStateInterface $form_state, YamlFormInterface $yamlform = NULL) {
    $this->yamlform = $yamlform;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirectUrl($this->getCancelUrl());
    if ($this->submissionStorage->getTotal($this->yamlform) < $this->batchLimit) {
      $this->submissionStorage->deleteAll($this->yamlform);
      drupal_set_message($this->getFinishedMessage());
    }
    else {
      $this->batchSet($this->yamlform);
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
   * @param \Drupal\yamlform\YamlFormInterface|NULL $yamlform
   *   A YAML form.
   */
  public function batchSet(YamlFormInterface $yamlform = NULL) {
    $parameters = [
      $yamlform,
      $this->submissionStorage->getMaxSubmissionId($yamlform),
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
    return $this->config('yamlform.settings')->get('batch.export_limit') ?: 500;
  }

  /**
   * Batch API callback; Delete submissions.
   *
   * @param \Drupal\yamlform\YamlFormInterface|NULL $yamlform
   *   A YAML form.
   * @param int $max_sid
   *   The max submission ID to be delete.
   * @param mixed|array $context
   *   The batch current context.
   */
  public function batchProcess(YamlFormInterface $yamlform = NULL, $max_sid, &$context) {
    if (empty($context['sandbox'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = $this->submissionStorage->getTotal($yamlform);
      $context['results']['yamlform'] = $yamlform;
    }

    // Track progress.
    $context['sandbox']['progress'] += $this->submissionStorage->deleteAll($yamlform, $this->getBatchLimit(), $max_sid);

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
