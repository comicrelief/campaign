<?php

namespace Drupal\yamlform\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\yamlform\YamlFormHandlerMessageInterface;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Defines the custom access control handler for the form entities.
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
   * Check whether the user can view submissions.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  static public function checkSubmissionAccess(AccountInterface $account) {
    return AccessResult::allowedIf($account->hasPermission('administer yamlform') || $account->hasPermission('administer yamlform submission') || $account->hasPermission('view any yamlform submission'));
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
   * Check that form submission has email and the user can update any form submission.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A form submission.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  static public function checkEmailAccess(YamlFormSubmissionInterface $yamlform_submission, AccountInterface $account) {
    $yamlform = $yamlform_submission->getYamlForm();
    if ($yamlform->access('submission_update_any')) {
      $handlers = $yamlform->getHandlers();
      foreach ($handlers as $handler) {
        if ($handler instanceof YamlFormHandlerMessageInterface) {
          return AccessResult::allowed();
        }
      }
    }
    return AccessResult::forbidden();
  }

  /**
   * Check whether the user can access an entity's form results.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  static public function checkEntityResultsAccess(EntityInterface $entity, AccountInterface $account) {
    return AccessResult::allowedIf($entity->access('update') && $entity->hasField('yamlform') && $entity->yamlform->entity);
  }

}
