<?php

/**
 * @file
 * Contains Drupal\yamlform\Form\YamlFormResultsClearForm.
 */

namespace Drupal\yamlform\Form;

use Drupal\Core\Url;

/**
 * Form for YAML form results clear form.
 */
class YamlFormResultsClearForm extends YamlFormSubmissionsDeleteFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'yamlform_results_clear';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete all submissions to %title form?', ['%title' => $this->yamlform->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.yamlform.results_submissions', ['yamlform' => $this->yamlform->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getMessage() {
    $this->t('Form %title submissions cleared.', ['%title' => $this->yamlform->label()]);
  }

}
