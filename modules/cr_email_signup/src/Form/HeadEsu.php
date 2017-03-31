<?php

namespace Drupal\cr_email_signup\Form;

/**
 * Concrete the Header sign up.
 */
class HeadEsu extends FundraiseSignUp {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cr_header_email_signup_form';
  }

}
