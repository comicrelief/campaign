<?php

namespace Drupal\cr_email_signup\Form;

class HeadEsu extends StandardSignUp {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cr_header_email_signup_form';
  }

}
