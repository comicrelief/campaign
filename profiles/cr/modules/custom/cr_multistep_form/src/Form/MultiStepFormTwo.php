<?php
/**
 * @file
 * Contains \Drupal\cr_multistep_form\Form\MultiStepFormTwo.
 */

namespace Drupal\cr_multistep_form\Form;

use Drupal\cr_multistep_form\Form\MultiStepFormBase;
use Drupal\Core\Form\FormStateInterface;
/**
 * Concrete implementation of Step Two.
 */
class MultiStepFormTwo extends MultiStepFormBase {

  /**
   * Get the Form Identifier.
   */
  public function getFormId() {
    return 'multistep_form_two';
  }

  /**
   * Build the Form Elements.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildForm($form, $form_state);

    // TODO: Age ranges need clarifying.
    $form['age_group'] = array(
      '#type' => 'select',
      '#title' => $this->t('Your age group'),
      '#default_value' => $this->store->get('age_group') ? $this->store->get('age_group') : '',
      '#options' => array(
        ' -- Select Age Group --',
        '18 - 25',
        '26 - 35',
        '36 - 45',
        '46+',
      ),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->store->set('age_group', $form_state->getValue('age_group'));
    $email_address = $this ->store ->get("email");
    parent::saveData();

    // TODO: Fire off to RabbitMQ with original email
    // TODO: Redirect to wherever we need/Thank you page.
    return TRUE;
  }

}
