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
  public function build() {
    $config = $this->getConfiguration();

    $form = \Drupal::formBuilder()->getForm('Drupal\cr_email_signup\Form\StandardSignUp');

    $form['initial_message'] = [
      '#markup' => "<div class='esu-initial-message'><h4>{$config['initial_message']}</h4></div>",
    ];

    $form['first_success_message'] = [
      '#markup' => "<div class='esu-first-success-message'><h4>{$config['first_success_message']}</h4></div>",
    ];

    $form['second_success_message'] = [
      '#markup' => "<div class='esu-second-success-message'><h4>{$config['second_success_message']}</h4></div>",
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['cr_email_signup_second_success_message'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Second Success Message'),
      '#description' => $this->t('Enter the success message for the second stage, if any'),
      '#default_value' => isset($config['second_success_message']) ? $config['second_success_message'] : '',
    );

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
