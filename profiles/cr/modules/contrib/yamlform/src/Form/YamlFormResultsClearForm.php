<?php

namespace Drupal\yamlform\Form;

use Drupal\Core\Url;

/**
 * Form for form results clear form.
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
    if ($this->sourceEntity) {
      $t_args = ['%title' => $this->sourceEntity->label()];
    }
    else {
      $t_args = ['%title' => $this->yamlform->label()];
    }
    return $this->t('Are you sure you want to delete all submissions to %title form?', $t_args);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    $route_name = $this->requestHandler->getRouteName($this->yamlform, $this->sourceEntity, 'yamlform.results_submissions');
    $route_parameters = $this->requestHandler->getRouteParameters($this->yamlform, $this->sourceEntity);
    return new Url($route_name, $route_parameters);
  }

  /**
   * {@inheritdoc}
   */
  public function getMessage() {
    if ($this->sourceEntity) {
      $t_args = ['%title' => $this->sourceEntity->label()];
    }
    else {
      $t_args = ['%title' => $this->yamlform->label()];
    }
    $this->t('Form %title submissions cleared.', $t_args);
  }

}
