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
class RegisterInterestSignUpBlock extends WorkplaceSignUpBlock implements BlockPluginInterface {

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

}
