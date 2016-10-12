<?php

namespace Drupal\yamlform_templates;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseDialogCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\yamlform\YamlFormDialogTrait;
use Drupal\yamlform\YamlFormSubmissionForm;

/**
 * Preview form submission form.
 */
class YamlFormTemplatesSubmissionPreviewForm extends YamlFormSubmissionForm {

  use YamlFormDialogTrait;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    if ($this->isModalDialog()) {
      // Disable validation.
      $form['#attributes']['novalidate'] = 'novalidate';

      // Display form title in modal.
      $form['title'] = [
        '#markup' => $this->getYamlForm()->label(),
        '#prefix' => '<h1>',
        '#suffix' => '</h1>',
        '#weight' => -101,
      ];

      // Remove type from 'actions' and add modal 'actions'.
      unset($form['actions']['#type']);
      $form['modal_actions'] = ['#type' => 'actions'];
      $form['modal_actions']['select'] = [
        '#type' => 'submit',
        '#value' => $this->t('Select'),
        '#button_type' => 'primary',
        '#ajax' => [
          'callback' => '::selectTemplate',
          'event' => 'click',
        ],
      ];
      $form['modal_actions']['close'] = [
        '#type' => 'submit',
        '#value' => $this->t('Close'),
        '#ajax' => [
          'callback' => '::closeDialog',
          'event' => 'click',
        ],
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($this->isModalDialog()) {
      $form_state->clearErrors();
    }
    else {
      parent::validateForm($form, $form_state);
    }
  }

  /**
   * Select template.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return bool|\Drupal\Core\Ajax\AjaxResponse
   *   An AJAX response that display validation error messages.
   */
  public function selectTemplate(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new RedirectCommand(Url::fromRoute('entity.yamlform.duplicate_form', ['yamlform' => $this->getYamlForm()->id()])->toString()));
    return $response;
  }

  /**
   * Close dialog.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return bool|\Drupal\Core\Ajax\AjaxResponse
   *   An AJAX response that display validation error messages.
   */
  public function closeDialog(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new CloseDialogCommand());
    return $response;
  }

}
