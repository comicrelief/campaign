<?php

namespace Drupal\cr_email_signup\Plugin\Block;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Provides a 'Register Interest Sign Up' block.
 *
 * @Block(
 *   id = "cr_email_signup_block_register_interest",
 *   admin_label = @Translation("Email Sign Up block: Register Interest"),
 * )
 */
class RegisterInterestSignUpBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    $form = \Drupal::formBuilder()->getForm('Drupal\cr_email_signup\Form\RegisterInterestSignUp');

    $form['initial_message'] = [
      '#markup' => "<div class='esu-initial-message'><h4>{$config['initial_message']}</h4></div>",
    ];

    $form['first_success_message'] = [
      '#markup' => "<div class='esu-first-success-message'><h4>{$config['first_success_message']}</h4></div>",
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account, $return_as_object = FALSE) {
    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['cr_email_signup_initial_message'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Initial Message'),
      '#description' => $this->t('Enter the initial message to show'),
      '#default_value' => isset($config['initial_message']) ? $config['initial_message'] : '',
    );

    $form['cr_email_signup_first_success_message'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('First Success Message'),
      '#description' => $this->t('Enter the success message'),
      '#default_value' => isset($config['first_success_message']) ? $config['first_success_message'] : '',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('initial_message', $form_state->getValue('cr_email_signup_initial_message'));
    $this->setConfigurationValue('first_success_message', $form_state->getValue('cr_email_signup_first_success_message'));
  }

}
