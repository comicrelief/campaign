<?php

namespace Drupal\ds\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Configure block fields.
 */
class CopyFieldForm extends FieldFormBase {

  /**
   * The type of the dynamic ds field.
   */
  const TYPE = 'copy';

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'ds_field_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $field_key = '') {
    $form = parent::buildForm($form, $form_state, $field_key);
    $field = $this->field;

    $manager = \Drupal::service('plugin.manager.ds');

    $fields = array();
    foreach ($manager->getDefinitions() as $plugin_id => $plugin_definition) {
      $entity_label = '';
      if (isset($plugin_definition['entity_type'])) {
        $entity_label .= ucfirst(str_replace('_', ' ', $plugin_definition['entity_type'])) . ' - ';
      }
      $fields[$plugin_id] = $entity_label . $plugin_definition['title'];
    }
    asort($fields);

    $form['ds_field_identity']['ds_plugin'] = array(
      '#type' => 'select',
      '#options' => $fields,
      '#title' => $this->t('Fields'),
      '#required' => TRUE,
      '#default_value' => isset($field['properties']['ds_plugin']) ? $field['properties']['ds_plugin'] : '',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getProperties(FormStateInterface $form_state) {
    return array(
      'ds_plugin' => $form_state->getValue('ds_plugin'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return CopyFieldForm::TYPE;
  }

  /**
   * {@inheritdoc}
   */
  public function getTypeLabel() {
    return 'Copy field';
  }

}
