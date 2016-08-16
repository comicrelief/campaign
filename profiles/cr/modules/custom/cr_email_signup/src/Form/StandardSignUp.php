<?php

namespace Drupal\cr_email_signup\Form;

/**
 * Concrete implementation of Step One.
 */
class StandardSignUp extends SignUp {

  /**
   * Get the Form Identifier.
   */
  public function getFormId() {
    return 'cr_email_signup_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQueueName() {
    return 'esu';
  }

}
