<?php
/**
 * @file
 * Contains \Drupal\cr_email_signup\Form\SignUpStepTwo.
 */

namespace Drupal\cr_email_signup\Form;

use Drupal\cr_email_signup\Form\MultiStepFormBase;
use Drupal\Core\Form\FormStateInterface;
/**
 * Concrete implementation of Step Two.
 */
class SignUpStepTwo extends MultiStepFormBase {

  /**
   * Get the Form Identifier.
   */
  public function getFormId() {
    return 'cr_email_signup_form_two';
  }

  /**
   * Build the Form Elements.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildForm($form, $form_state);

    // TODO: School Phases need clarifying.
    $form['school_phase'] = array(
      '#type' => 'select',
      '#title' => $this->t('School Phase'),
      '#default_value' => $this->store->get('school_phase') ? $this->store->get('school_phase') : '',
      '#options' => array(
        0 => ' -- Select School Phase --',
        'EY' => 'Early Years or Nursery',
        'PY' => 'Primary',
        'SY' => 'Secondary',
        'FE' => 'Further Education or Sixth-Form College',
        'HE' => 'Higher Education',
        'OH' => 'Other',
      ),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $school_phase = $form_state->getValue('school_phase');
    $email_address = $this->store->get('email');
    $this->store->set('school_phase', $school_phase);
    // ageGroup key is an assumption.
    $queue_message = array(
      'transSourceURL' => \Drupal::service('path.current')->getPath(),
      'transSource' => "[Campaign]_[Device]_ESU_[PageElementSource]",
      'timestamp' => time(),
      'emailAddress' => $email_address,
      'schoolPhase' => $school_phase,
    );

    parent::queueMessage($queue_message);
    parent::saveData();

    // TODO: Redirect to wherever we need/Thank you page.
    return TRUE;
  }

}
