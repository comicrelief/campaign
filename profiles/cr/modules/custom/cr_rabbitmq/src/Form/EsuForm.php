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
      '#title' => $this->t('Your .com email address.'),
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

    /** @var QueueFactory $queue_factory */
    $queue_factory = \Drupal::service('queue');
    /** @var ReliableQueueInterface $queue */
    $queue = $queue_factory->get('cr');
    $item = $form_state->getValue('email');
    $queue->createItem($item);

    $queue2 = $queue_factory->get('cr3');
    $queue2->createItem($item);
  }

}
