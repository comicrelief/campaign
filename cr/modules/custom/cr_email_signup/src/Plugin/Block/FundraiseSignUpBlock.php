<?php

namespace Drupal\cr_email_signup\Plugin\Block;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Provides a 'Fundraise Sign Up' block.
 *
 * @Block(
 *   id = "cr_email_signup_block_fundraise",
 *   admin_label = @Translation("Email Sign Up block: Fundraise"),
 * )
 */
class FundraiseSignUpBlock extends WorkplaceSignUpBlock implements BlockPluginInterface {

  /**
   * Return the form.
   */
  protected function getEsuForm() {
    return \Drupal::formBuilder()->getForm('Drupal\cr_email_signup\Form\FundraiseSignUp');
  }

}
