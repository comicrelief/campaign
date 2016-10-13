<?php

namespace Drupal\yamlform\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormInterface;

/**
 * Form for deleting a form handler.
 */
class YamlFormHandlerDeleteForm extends ConfirmFormBase {

  /**
   * The form containing the form handler to be deleted.
   *
   * @var \Drupal\yamlform\YamlFormInterface
   */
  protected $yamlform;

  /**
   * The form handler to be deleted.
   *
   * @var \Drupal\yamlform\YamlFormHandlerInterface
   */
  protected $yamlformHandler;

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the @handler handler from the %yamlform form?', ['%yamlform' => $this->yamlform->label(), '@handler' => $this->yamlformHandler->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->yamlform->urlInfo('handlers-form');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'yamlform_handler_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, YamlFormInterface $yamlform = NULL, $yamlform_handler = NULL) {
    $this->yamlform = $yamlform;
    $this->yamlformHandler = $this->yamlform->getHandler($yamlform_handler);

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->yamlform->deleteYamlFormHandler($this->yamlformHandler);
    drupal_set_message($this->t('The form handler %name has been deleted.', ['%name' => $this->yamlformHandler->label()]));
    $form_state->setRedirectUrl($this->yamlform->urlInfo('handlers-form'));
  }

}
