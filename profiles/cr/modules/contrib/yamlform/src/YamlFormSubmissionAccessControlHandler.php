<?php

namespace Drupal\yamlform;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the form submission entity type.
 *
 * @see \Drupal\yamlform\Entity\YamlFormSubmission.
 */
class YamlFormSubmissionAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  public function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    // Allow users with 'view any yamlform submission' to view all submissions.
    if ($operation == 'view' && $account->hasPermission('view any yamlform submission')) {
      return AccessResult::allowed();
    }

    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $entity */
    $yamlform = $entity->getYamlForm();
    if ($yamlform->access('update') || $yamlform->checkAccessRules($operation, $account, $entity)) {
      return AccessResult::allowed();
    }

    return parent::checkAccess($entity, $operation, $account);
  }

}
