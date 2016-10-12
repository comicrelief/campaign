<?php

namespace Drupal\yamlform;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the form entity type.
 *
 * @see \Drupal\yamlform\Entity\YamlForm.
 */
class YamlFormEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    if ($account->hasPermission('create yamlform')) {
      return AccessResult::allowed()->cachePerPermissions();
    }
    else {
      return parent::checkCreateAccess($account, $context, $entity_bundle);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var  \Drupal\yamlform\YamlFormInterface $entity */
    // Check 'view' using 'create' custom form submission access rules.
    // Viewing a form is the same as creating a form submission.
    if ($operation == 'view') {
      return AccessResult::allowed();
    }

    $uid = $entity->getOwnerId();
    $is_owner = ($account->isAuthenticated() && $account->id() == $uid);
    // Check if 'update' or 'delete' of 'own' or 'any' form is allowed.
    if ($account->isAuthenticated()) {
      switch ($operation) {
        case 'update':
          if ($account->hasPermission('edit any yamlform') || ($account->hasPermission('edit own yamlform') && $is_owner)) {
            return AccessResult::allowed()->cachePerPermissions()->cachePerUser()->addCacheableDependency($entity);
          }
          break;

        case 'duplicate':
          if ($entity->isTemplate() || ($account->hasPermission('edit any yamlform') || ($account->hasPermission('edit own yamlform') && $is_owner))) {
            return AccessResult::allowed()->cachePerPermissions()->cachePerUser()->addCacheableDependency($entity);
          }
          break;

        case 'delete':
          if ($account->hasPermission('delete any yamlform') || ($account->hasPermission('delete own yamlform') && $is_owner)) {
            return AccessResult::allowed()->cachePerPermissions()->cachePerUser()->addCacheableDependency($entity);
          }
          break;
      }
    }

    // Check submission_* operation.
    if (strpos($operation, 'submission_') === 0) {
      // Allow users with 'view any yamlform submission' to view all submissions.
      if ($operation == 'submission_view_any' && $account->hasPermission('view any yamlform submission')) {
        return AccessResult::allowed();
      }

      // Completely block access to a template if the user can't create new
      // Forms.
      if ($operation == 'submission_page' && $entity->isTemplate() && !$entity->access('create')) {
        return AccessResult::forbidden()->cachePerPermissions()->cachePerUser()->addCacheableDependency($entity);
      }

      // Check custom form submission access rules.
      if ($this->checkAccess($entity, 'update', $account)->isAllowed() || $entity->checkAccessRules(str_replace('submission_', '', $operation), $account)) {
        return AccessResult::allowed()->cachePerPermissions()->cachePerUser()->addCacheableDependency($entity);
      }
    }

    return parent::checkAccess($entity, $operation, $account);
  }

}
