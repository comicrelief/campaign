<?php
/**
 * @file
 * Contains \Drupal\cr_email_signup\Form\SignUpStepOne.
 */

namespace Drupal\cr_email_signup\Form;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cr_email_signup\Form\MultiStepFormBase;
/**
 * Concrete implementation of Step One.
 */
class SignUpStepOne extends MultiStepFormBase implements FormInterface {

  /**
   * Get the Form Identifier.
   */
  public function getFormId() {

    return 'cr_email_signup_form_one';
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
    $email_address = $form_state->getValue('email');
    // TODO: make key transSource dynamic/configurable.
    $queue_message = array(
      'transSourceURL' => \Drupal::service('path.current')->getPath(),
      'transSource' => "[Campaign]_[Device]_ESU_[PageElementSource]",
      'timestamp' => time(),
      'emailAddress' => $email_address,
    );

    $this->store->set('email', $email_address);

    parent::queueMessage($queue_message);

    $form_state->setRedirect('cr_email_signup.step_two');
  }

}
