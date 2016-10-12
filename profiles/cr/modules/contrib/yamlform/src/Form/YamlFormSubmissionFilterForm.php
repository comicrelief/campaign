<?php

namespace Drupal\yamlform\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the form submission filter form.
 */
class YamlFormSubmissionFilterForm extends YamlFormFilterFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'yamlform_submission_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $search = NULL, $state = NULL, array $state_options = []) {
    $form = parent::buildForm($form, $form_state, $search, $state, $state_options);
    $form['filter']['#title'] = $this->t('Filter submissions');
    $form['filter']['search']['#title'] = $this->t('Filter by submitted data and/or notes');
    $form['filter']['search']['#placeholder'] = $this->t('Filter by submitted data and/or notes');
    return $form;
  }

}
