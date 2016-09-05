<?php

namespace Drupal\cr_email_signup\Plugin\Block;

/**
 * Provides a 'Register Interest Sign Up' block.
 *
 * @Block(
 *   id = "cr_email_signup_block_register_interest",
 *   admin_label = @Translation("Email Sign Up block: Register Interest"),
 * )
 */
class RegisterInterestSignUpBlock extends WorkplaceSignUpBlock {

  /**
   * {@inheritdoc}
   */
  protected function getEsuForm() {
    return \Drupal::formBuilder()->getForm('Drupal\cr_email_signup\Form\RegisterInterestSignUp');
  }

}
