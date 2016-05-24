<?php
/**
 * @file
 * Contains \Drupal\cr_email_signup\Plugin\Block\SignUpBlock.
 */

namespace Drupal\cr_email_signup\Plugin\Block;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Provides a 'Sign Up' block.
 *
 * @Block(
 *   id = "cr_email_signup_block",
 *   admin_label = @Translation("Email Sign Up block"),
 * )
 */
class SignUpBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    $form = \Drupal::formBuilder()->getForm('Drupal\cr_email_signup\Form\SignUp');

    $form['initial_message'] = [
      '#markup' => "<div class='esu-initial-message'>{$config['initial_message']}</div>",
    ];

    $form['first_success_message'] = [
      '#markup' => "<div class='esu-first-success-message'>{$config['first_success_message']}</div>",
    ];

    $form['second_success_message'] = [
      '#markup' => "<div class='esu-second-success-message'>{$config['second_success_message']}</div>",
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
      '#description' => $this->t('Enter the success message for the first stage'),
      '#default_value' => isset($config['first_success_message']) ? $config['first_success_message'] : '',
    );

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
    $this->setConfigurationValue('initial_message', $form_state->getValue('cr_email_signup_initial_message'));
    $this->setConfigurationValue('first_success_message', $form_state->getValue('cr_email_signup_first_success_message'));
    $this->setConfigurationValue('second_success_message', $form_state->getValue('cr_email_signup_second_success_message'));
  }

}
