<?php

namespace Drupal\yamlform_ui\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a duplicate form for a form element.
 */
class YamlFormUiElementDuplicateForm extends YamlFormUiElementFormBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, YamlFormInterface $yamlform = NULL, $key = NULL) {
    if (empty($key)) {
      throw new NotFoundHttpException();
    }

    $this->element = $yamlform->getElementDecoded($key);
    if ($this->element === NULL) {
      throw new NotFoundHttpException();
    }

    $element_initialized = $yamlform->getElement($key);

    $form['#title'] = $this->t('Duplicate @title element', [
      '@title' => (!empty($this->element['#title'])) ? $this->element['#title'] : $key,
    ]);

    $this->action = $this->t('created');
    return parent::buildForm($form, $form_state, $yamlform, NULL, $element_initialized['#yamlform_parent_key']);
  }

}
