<?php

namespace Drupal\yamlform\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Form for form submission purge form.
 */
class YamlFormSubmissionsPurgeForm extends YamlFormSubmissionsDeleteFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'yamlform_submissions_purge';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete all submissions?');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Purge');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.yamlform_submission.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getFinishedMessage() {
    return $this->t('Form submissions purged.');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $submission_total = \Drupal::entityQuery('yamlform_submission')->count()->execute();
    $form_total = \Drupal::entityQuery('yamlform')->count()->execute();
    $t_args = [
      '@submission_total' => $submission_total,
      '@submissions' => $this->formatPlural($submission_total, $this->t('submission'), $this->t('submissions')),
      '@form_total' => $form_total,
      '@forms' => $this->formatPlural($form_total, $this->t('form'), $this->t('forms')),
    ];

    $form['confirm'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Are you sure you want to delete @submission_total @submissions in @form_total @forms?', $t_args),
      '#required' => TRUE,
      '#weight' => -10,
    ];

    return $form;
  }

}
