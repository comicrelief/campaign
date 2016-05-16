<?php
/**
 * @file
 * Contains \Drupal\cr_email_signup\Plugin\Block\SignUpBlock.
 */

namespace Drupal\cr_email_signup\Plugin\Block;

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
class SignUpBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return \Drupal::formBuilder()->getForm('Drupal\cr_email_signup\Form\SignUp');
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account, $return_as_object = FALSE) {
    return AccessResult::allowed();
  }

}
