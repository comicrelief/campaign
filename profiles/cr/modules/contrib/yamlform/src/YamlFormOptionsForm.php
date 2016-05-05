<?php

/**
 * @file
 * Contains Drupal\yamlform\YamlFormOptionsForm.
 */

namespace Drupal\yamlform;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Base for controller for YAML form options.
 */
class YamlFormOptionsForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $yamlform_options = $this->entity;

    $form['#attached']['library'][] = 'yamlform/yamlform.codemirror';
    $form['#attached']['library'][] = 'yamlform/' . (file_exists(DRUPAL_ROOT . '/libraries/codemirror') ? 'libraries' : 'cdn') . '.codemirror';

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#required' => TRUE,
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

    $form['options'] = [
      '#type' => 'yamlform_codemirror_yaml',
      '#title' => $this->t('Options (YAML)'),
      '#description' => $this->t('Key-value pairs MUST be specified as "safe_key: \'Some readable option\'". Use of only alphanumeric characters and underscores is recommended in keys. One option per line. Option groups can be created by using just the group name followed by indented group options.'),
      '#required' => TRUE,
      '#default_value' => $yamlform_options->get('options'),
    ];

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\yamlform\YamlFormOptionsInterface $yamlform_options */
    $yamlform_options = $this->getEntity();
    $yamlform_options->save();

    $this->logger('yamlform')->notice('YAML form options @label saved.', ['@label' => $yamlform_options->label()]);
    drupal_set_message($this->t('YAML form options %label saved.', ['%label' => $yamlform_options->label()]));

    $form_state->setRedirect('entity.yamlform_options.collection');
  }

}
