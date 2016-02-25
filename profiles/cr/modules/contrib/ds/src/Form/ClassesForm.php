<?php

/**
 * @file
 * Contains \Drupal\ds\Form\ClassesForm.
 */

namespace Drupal\ds\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configures classes used by wrappers and regions.
 */
class ClassesForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ds_classes_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ds.settings');

    $form['regions'] = array(
      '#type' => 'textarea',
      '#title' => t('CSS classes for regions'),
      '#default_value' => implode("\n", $config->get('classes.region')),
      '#description' => t('Configure CSS classes which you can add to regions on the "manage display" screens. Add multiple CSS classes line by line.<br />If you want to have a friendly name, separate class and friendly name by |, but this is not required. eg:<br /><em>class_name_1<br />class_name_2|Friendly name<br />class_name_3</em>')
    );

    // Only show field classes if DS extras module is enabled
    if (\Drupal::moduleHandler()->moduleExists('ds_extras')) {
      $form['fields'] = array(
        '#type' => 'textarea',
        '#title' => t('CSS classes for fields'),
        '#default_value' =>  implode("\n", $config->get('classes.field')),
        '#description' => t('Configure CSS classes which you can add to fields on the "manage display" screens. Add multiple CSS classes line by line.<br />If you want to have a friendly name, separate class and friendly name by |, but this is not required. eg:<br /><em>class_name_1<br />class_name_2|Friendly name<br />class_name_3</em>')
      );
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    // Prepare region classes
    $region_classes = array();
    $regions = $form_state->getValue('regions');
    if (!empty($regions)) {
      $region_classes = explode("\n", str_replace("\r", '', $form_state->getValue('regions')));
    }

    // Prepare field classes
    $field_classes = array();
    $fields = $form_state->getValue('fields');
    if (!empty($fields)) {
      $field_classes = explode("\n", str_replace("\r", '', $form_state->getValue('fields')));
    }

    $config = $this->config('ds.settings');
    $config->set('classes.region', $region_classes)
      ->set('classes.field', $field_classes)
      ->save();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return array(
      'ds.settings'
    );
  }
}
