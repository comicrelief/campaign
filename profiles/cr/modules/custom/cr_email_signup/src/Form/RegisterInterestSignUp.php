<?php

namespace Drupal\cr_email_signup\Form;

/**
 * Concrete implementation of Step One.
 */
class RegisterInterestSignUp extends SignUp {

  protected $transType = 'RegisterInterest';

  /**
   * Get the Form Identifier.
   */
  public function getFormId() {
    return 'cr_email_signup_register_interest_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQueueName() {
    return 'register_interest';
  }

}
