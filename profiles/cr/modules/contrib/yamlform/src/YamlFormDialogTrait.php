<?php

namespace Drupal\yamlform;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\EventSubscriber\MainContentViewSubscriber;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Trait class form dialogs.
 */
trait YamlFormDialogTrait {

  /**
   * Is the current request for an AJAX modal dialog.
   *
   * @return bool
   *   TRUE is the current request if for an AJAX modal dialog.
   */
  protected function isModalDialog() {
    $wrapper_format = $this->getRequest()
      ->get(MainContentViewSubscriber::WRAPPER_FORMAT);
    return (in_array($wrapper_format, [
      'drupal_ajax',
      'drupal_modal',
    ])) ? TRUE : FALSE;
  }

  /**
   * Add modal dialog support to a form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form with modal dialog support.
   */
  protected function buildDialog(array &$form, FormStateInterface $form_state) {
    if ($this->isModalDialog()) {
      $form['actions']['submit']['#ajax'] = [
        'callback' => '::submitForm',
        'event' => 'click',
      ];
      $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
      $form['#prefix'] = '<div id="yamlform-dialog">';
      $form['#suffix'] = '</div>';
    }
    return $form;
  }

  /**
   * Display validation error messages in modal dialog.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return bool|\Drupal\Core\Ajax\AjaxResponse
   *   An AJAX response that display validation error messages.
   */
  protected function validateDialog(array &$form, FormStateInterface $form_state) {
    if ($this->isModalDialog() && $form_state->hasAnyErrors()) {
      unset($form['#prefix'], $form['#suffix']);
      $form['status_messages'] = [
        '#type' => 'status_messages',
        '#weight' => -10,
      ];
      $response = new AjaxResponse();
      $response->addCommand(new HtmlCommand('#yamlform-dialog', $form));
      return $response;
    }
    return FALSE;
  }

  /**
   * Handler dialog redirect after form is submitted.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Drupal\Core\Url $url
   *   Redirect URL.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse|null
   *   An AJAX redirect response or null if redirection is being handled by the
   *   $form_state.
   */
  protected function redirectForm(array &$form, FormStateInterface $form_state, Url $url) {
    if ($this->isModalDialog()) {
      $response = new AjaxResponse();
      $response->addCommand(new RedirectCommand($url->toString()));
      return $response;
    }
    else {
      $form_state->setRedirectUrl($url);
      return NULL;
    }
  }

}
