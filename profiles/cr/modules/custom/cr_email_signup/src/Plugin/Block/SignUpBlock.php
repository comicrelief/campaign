<?php

namespace Drupal\cr_email_signup\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Sign Up' block.
 *
 * @Block(
 *   id = "cr_email_signup_block",
 *   admin_label = @Translation("Email Sign Up block: Standard"),
 * )
 */
class SignUpBlock extends WorkplaceSignUpBlock {

  /**
   * {@inheritdoc}
   */
  protected function getEsuForm() {
    return \Drupal::formBuilder()->getForm('Drupal\cr_email_signup\Form\StandardSignUp');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['cr_email_signup_second_success_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Second Success Message'),
      '#description' => $this->t('Enter the success message for the second stage, if any'),
      '#default_value' => isset($config['second_success_message']) ? $config['second_success_message'] : '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->setConfigurationValue('second_success_message', $form_state->getValue('cr_email_signup_second_success_message'));
  }

}
