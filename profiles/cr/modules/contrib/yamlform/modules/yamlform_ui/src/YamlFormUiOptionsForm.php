<?php

namespace Drupal\yamlform_ui;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\Element\YamlFormOptions;
use Drupal\yamlform\YamlFormOptionsForm;

/**
 * Base for controller for form option UI.
 */
class YamlFormUiOptionsForm extends YamlFormOptionsForm {

  /**
   * {@inheritdoc}
   */
  public function editForm(array $form, FormStateInterface $form_state) {
    $yamlform_options = $this->entity;

    $form['options'] = [
      '#type' => 'yamlform_options',
      '#mode' => 'yaml',
      '#title' => $this->t('Options'),
      '#title_display' => 'invisible',
      '#empty_options' => 10,
      '#add_more' => 10,
      '#required' => TRUE,
      '#default_value' => Yaml::decode($yamlform_options->get('options')),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // @todo Determine why options.options and options.add are being included.
    // Best guess is the yamlform_options element has not been validated.
    if (isset($values['options']['options'])) {
      $options = (is_array($values['options']['options'])) ? YamlFormOptions::convertValuesToOptions($values['options']['options']) : [];
    }
    elseif (isset($values['options'])) {
      $options = (is_array($values['options'])) ? $values['options'] : [];
    }
    else {
      $options = [];
    }
    $entity->set('options', Yaml::encode($options));
    unset($values['options']);

    foreach ($values as $key => $value) {
      $entity->set($key, $value);
    }
  }

}
