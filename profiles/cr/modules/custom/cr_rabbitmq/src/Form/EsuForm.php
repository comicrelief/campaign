<?php

/**
 * @file
 * Contains \Drupal\cr_rabbitmq\Form\ContributeForm.
 */

namespace Drupal\cr_rabbitmq\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Contribute form.
 */
class EsuForm extends FormBase {

  /**
   * rabbitmq.
   */
  protected $rabbit;
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'esu_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Your .com email address.')
    ];
    $form['show'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    drupal_set_message($this->t('Your email address is @email', ['@email' => $form_state->getValue('email')]));

    $this->rabbit = \Drupal::service('cr_rabbitmq.producer');
    $this->rabbit->sendMQ($form_state->getValue('email'));
  }
}
