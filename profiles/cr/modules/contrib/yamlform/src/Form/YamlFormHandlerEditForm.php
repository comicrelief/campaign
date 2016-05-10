<?php

/**
 * @file
 * Contains \Drupal\yamlform\Form\YamlFormHandlerEditForm.
 */

namespace Drupal\yamlform\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormInterface;

/**
 * Provides an edit form for YAML form handlers.
 */
class YamlFormHandlerEditForm extends YamlFormHandlerFormBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, YamlFormInterface $yamlform = NULL, $yamlform_handler = NULL) {
    $form = parent::buildForm($form, $form_state, $yamlform, $yamlform_handler);

    $form['#title'] = $this->t('Edit %label handler', ['%label' => $this->yamlformHandler->label()]);
    $form['actions']['submit']['#value'] = $this->t('Update');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareYamlFormHandler($yamlform_handler) {
    return $this->yamlform->getHandler($yamlform_handler);
  }

}
