<?php

/**
 * @file
 * Contains \Drupal\rabbitmq_example\Form\ContributeForm.
 */

namespace Drupal\rabbitmq_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Contribute form.
 */
class ExampleForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rabbitmq_example_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Send an email address to the queue.'),
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
    // Get the data you want to send to the queue.
    $data = $form_state->getValue('email');

    // Get the queue config and send it to the data to the queue.
    $queue_factory = \Drupal::service('queue');
    $queue = $queue_factory->get('queue1');
    $queue->createItem($data);

    // Send some feedback.
    drupal_set_message(
      $this->t('You sent to the queue: @email', [
        '@email' => $form_state->getValue('email'),
      ])
    );
  }

}
