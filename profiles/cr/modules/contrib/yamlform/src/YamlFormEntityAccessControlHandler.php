<?php

/**
 * @file
 * Contains \Drupal\yamlform\YamlFormEntityAccessControlHandler.
 */

namespace Drupal\yamlform;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the YAML form entity type.
 *
 * @see \Drupal\yamlform\Entity\YamlForm.
 */
class YamlFormEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  public function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var  \Drupal\yamlform\YamlFormInterface $entity */
    // Check 'view' using 'create' custom YAML form submission access rules.
    // Viewing a YAML form is the same as creating a YAML form submission.
    if ($operation == 'view' && $entity->checkAccessRules('create', $account)) {
      return AccessResult::allowed();
    }

    // Check custom YAML form submission access rules.
    if (strpos($operation, 'submission_') === 0 && $entity->checkAccessRules(str_replace('submission_', '', $operation), $account)) {
      return AccessResult::allowed();
    }

    return parent::checkAccess($entity, $operation, $account);
  }

}
