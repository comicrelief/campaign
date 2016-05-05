<?php

/**
 * @file
 * Contains Drupal\yamlform\YamlFormEntityForm.
 */

namespace Drupal\yamlform;

use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Base for controller for YAML form.
 */
class YamlFormEntityForm extends BundleEntityFormBase {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    switch ($this->operation) {
      case 'duplicate':
        // Customize title for duplicate form.
        $form['#title'] = $this->t('<em>Duplicate YAML form</em> @label', ['@label' => $this->entity->label()]);
        $this->entity = $this->entity->createDuplicate();
        break;

      case 'default':
        // Display message for new YAML forms that have not been submitted.
        // TODO: Figure out how to determine POST request using $form_state.
        if ($this->getEntity()->isNew() && empty($_POST)) {
          drupal_set_message(t('Below are some default inputs to get you started. You can also duplicate existing <a href=":templates_href">templates</a>.', [':templates_href' => Url::fromRoute('entity.yamlform.collection', [], ['query' => ['search' => 'Template:']])->toString()]));
        }
        break;
    }

    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    $yamlform = $this->entity;

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => 255,
      '#default_value' => $yamlform->label(),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $yamlform->id(),
      '#machine_name' => [
        'exists' => '\Drupal\yamlform\Entity\YamlForm::load',
        'source' => ['title'],
      ],
      '#disabled' => (bool) $yamlform->id() && $this->operation != 'duplicate',
      '#required' => TRUE,
    ];
    $form['description'] = [
      '#type' => 'yamlform_codemirror_html',
      '#title' => $this->t('Administrative description'),
      '#default_value' => $yamlform->get('description'),
      '#rows' => 2,
    ];

    $default_value = $yamlform->get('inputs');
    if (!$default_value && $yamlform->isNew()) {
      $default_value = $this->configFactory()->get('yamlform.settings')->get('inputs.default_inputs');
    }
    $t_args = [
      ':form_api_href' => 'https://www.drupal.org/node/37775',
      ':render_api_href' => 'https://www.drupal.org/developing/api/8/render',
      ':yaml_href' => 'https://en.wikipedia.org/wiki/YAML',
    ];
    $form['inputs'] = [
      '#type' => 'yamlform_codemirror_yaml',
      '#title' => $this->t('Inputs (YAML)'),
      '#description' => $this->t('Enter a <a href=":form_api_href">Form API (FAPI)</a> and/or a <a href=":render_api_href">Render Array</a> as <a href=":yaml_href">YAML</a>.', $t_args),
      '#default_value' => $default_value ,
      '#required' => TRUE,
    ];

    $form = parent::form($form, $form_state);
    return $this->protectBundleIdElement($form);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Validate inputs YAML.
    if ($messages = \Drupal::service('yamlform.inputs_validator')->validate($this->getEntity())) {
      $form_state->setErrorByName('inputs');
      foreach ($messages as $message) {
        drupal_set_message($message, 'error');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    $yamlform = $this->getEntity();
    $yamlform->save();

    $this->logger('yamlform')->notice('YAML form @label inputs saved.', ['@label' => $yamlform->label()]);
    drupal_set_message($this->t('YAML form %label inputs saved.', ['%label' => $yamlform->label()]));

    $form_state->setRedirectUrl($yamlform->toUrl());
  }

}
