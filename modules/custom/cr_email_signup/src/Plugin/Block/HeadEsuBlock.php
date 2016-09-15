<?php

namespace Drupal\cr_email_signup\Plugin\Block;

/**
 * Provides a 'Head Sign Up' block.
 *
 * @Block(
 *   id = "cr_email_signup_block_head",
 *   admin_label = @Translation("Email Sign Up block: Head"),
 * )
 */
class HeadEsuBlock extends SignUpBlock {

  /**
   * {@inheritdoc}
   */
  protected function getEsuForm() {
    return \Drupal::formBuilder()->getForm('Drupal\cr_email_signup\Form\HeadEsu');
  }

}
