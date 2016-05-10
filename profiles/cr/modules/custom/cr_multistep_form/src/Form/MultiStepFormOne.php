<?php
/**
 * @file
 * Contains \Drupal\cr_multistep_form\Form\MultiStepFormOne.
 */

namespace Drupal\cr_multistep_form\Form;

use Drupal\Core\Form\FormStateInterface;
/**
 * Concrete implementation of Step One.
 */
class MultiStepFormOne extends MultiStepFormBase {

  /**
   * Get the Form Identifier.
   */
  public function getFormId() {

    return 'multistep_form_one';
  }

  /**
   * Build the Form Elements.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildForm($form, $form_state);

    $form['email'] = array(
      '#type' => 'email',
      '#title' => $this->t('Your email address'),
      '#default_value' => $this->store->get('email') ? $this->store->get('email') : '',
    );

    $form['actions']['submit']['#value'] = $this->t('Go');
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->store->set('email', $form_state->getValue('email'));
    $queue_name = 'queue1';
    $queue_factory = \Drupal::service('queue');
    $queue = $queue_factory->get($queue_name);
    $queue->createItem($form_state->getValue('email'));
    $form_state->setRedirect('cr_multistep_form.multistep_two');
  }

}
