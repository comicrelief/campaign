<?php

namespace Drupal\yamlform;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Controller for form submission notes.
 */
class YamlFormSubmissionNotesForm extends ContentEntityForm {

  use YamlFormDialogTrait;

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\yamlform\YamlFormRequestInterface $request_handler */
    $request_handler = \Drupal::service('yamlform.request');
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
    /** @var \Drupal\Core\Entity\EntityInterface $source_entity */
    list($yamlform_submission, $source_entity) = $request_handler->getYamlFormSubmissionEntities();

    $form['navigation'] = [
      '#theme' => 'yamlform_submission_navigation',
      '#yamlform_submission' => $yamlform_submission,
      '#access' => $this->isModalDialog() ? FALSE : TRUE,
    ];
    $form['information'] = [
      '#theme' => 'yamlform_submission_information',
      '#yamlform_submission' => $yamlform_submission,
      '#source_entity' => $source_entity,
      '#open' => FALSE,
      '#access' => $this->isModalDialog() ? FALSE : TRUE,
    ];

    $form['notes'] = [
      '#type' => 'yamlform_codemirror',
      '#title' => $this->t('Administrative notes'),
      '#description' => $this->t('Enter notes about this submission. These notes are only visible to submission administrators.'),
      '#default_value' => $yamlform_submission->getNotes(),
    ];
    $form['sticky'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Star/flag the status of this submission.'),
      '#default_value' => $yamlform_submission->isSticky(),
      '#return_value' => TRUE,
      '#access' => $this->isModalDialog() ? FALSE : TRUE,
    ];
    $form['#attached']['library'][] = 'yamlform/yamlform.admin';
    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    unset($actions['delete']);
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    drupal_set_message($this->t('Submission @sid notes saved.', ['@sid' => '#' . $this->entity->id()]));
  }

}
