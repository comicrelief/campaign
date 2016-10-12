<?php

namespace Drupal\yamlform\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the form filter form.
 */
class YamlFormEntityFilterForm extends YamlFormFilterFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'yamlform_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $search = NULL, $state = NULL, $state_options = []) {
    $form = parent::buildForm($form, $form_state, $search, $state, $state_options);
    $form['filter']['#title'] = $this->t('Filter forms');
    $form['filter']['search']['#title'] = $this->t('Filter by title, description, or elements');
    $form['filter']['search']['#autocomplete_route_name'] = 'entity.yamlform.autocomplete';
    $form['filter']['search']['#placeholder'] = $this->t('Filter by title, description, or elements');
    return $form;
  }

}
