<?php

namespace Drupal\cr_email_signup\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;

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
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $form['cr_email_signup_initial_message']['#type'] = 'textarea';
    return $form;
  }
  /**
   * {@inheritdoc}
   */
  protected function getEsuForm() {
    return \Drupal::formBuilder()->getForm('Drupal\cr_email_signup\Form\RegisterInterestSignUp');
  }

}
