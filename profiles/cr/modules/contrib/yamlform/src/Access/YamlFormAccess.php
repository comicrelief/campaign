<?php

/**
 * @file
 * Definition of Drupal\yamlform\Access\YamlFormAccess.
 */

namespace Drupal\yamlform\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\yamlform\YamlFormHandlerMessageInterface;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Defines the custom access control handler for the YAML form entities.
 */
class YamlFormAccess {

  /**
   * Check whether the user has 'administer yamlform' or 'administer yamlform submission' permission.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  static public function checkAdminAccess(AccountInterface $account) {
    return AccessResult::allowedIf($account->hasPermission('administer yamlform') || $account->hasPermission('administer yamlform submission'));
  }

  /**
   * Check whether the user has 'administer' or 'overview' permission.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  static public function checkOverviewAccess(AccountInterface $account) {
    return AccessResult::allowedIf($account->hasPermission('administer yamlform') || $account->hasPermission('administer yamlform submission') || $account->hasPermission('access yamlform overview'));
  }

  /**
   * Check that YAML form submission has email and the user can update any YAML form submission.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  static public function checkEmailAccess(YamlFormSubmissionInterface $yamlform_submission, AccountInterface $account) {
    $yamlform = $yamlform_submission->getYamlForm();
    if ($yamlform->access('update_any')) {
      $handlers = $yamlform->getHandlers();
      foreach ($handlers as $handler) {
        if ($handler instanceof YamlFormHandlerMessageInterface) {
          return AccessResult::allowed();
        }
      }
    }
    return AccessResult::forbidden();
  }

}
