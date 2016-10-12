<?php

namespace Drupal\yamlform;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Base for controller for form options.
 */
class YamlFormOptionsForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\yamlform\YamlFormOptionsInterface $yamlform_options */
    $yamlform_options = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#required' => TRUE,
      '#attributes' => ($yamlform_options->isNew()) ? ['autofocus' => 'autofocus'] : [],
      '#default_value' => $yamlform_options->label(),
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#machine_name' => [
        'exists' => '\Drupal\yamlform\Entity\YamlFormOptions::load',
      ],
      '#required' => TRUE,
      '#disabled' => !$yamlform_options->isNew(),
      '#default_value' => $yamlform_options->id(),
    ];

    // Call the isolated edit form that can be overridden by the
    // yamlform_ui.module.
    $form = $this->editForm($form, $form_state);

    return parent::form($form, $form_state);
  }

  /**
   * Edit form options source code form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  protected function editForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\yamlform\YamlFormOptionsInterface $yamlform_options */
    $yamlform_options = $this->entity;

    $form['options'] = [
      '#type' => 'yamlform_codemirror',
      '#mode' => 'yaml',
      '#title' => $this->t('Options (YAML)'),
      '#description' => $this->t('Key-value pairs MUST be specified as "safe_key: \'Some readable option\'". Use of only alphanumeric characters and underscores is recommended in keys. One option per line. Option groups can be created by using just the group name followed by indented group options.'),
      '#required' => TRUE,
      '#default_value' => $yamlform_options->get('options'),
    ];
    $form['#attached']['library'][] = 'yamlform/yamlform.codemirror.yaml';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\yamlform\YamlFormOptionsInterface $yamlform_options */
    $yamlform_options = $this->getEntity();
    $yamlform_options->save();

    $this->logger('yamlform')->notice('Form options @label saved.', ['@label' => $yamlform_options->label()]);
    drupal_set_message($this->t('Form options %label saved.', ['%label' => $yamlform_options->label()]));

    $form_state->setRedirect('entity.yamlform_options.collection');
  }

}
