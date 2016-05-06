<?php

/**
 * @file
 * Contains \Drupal\yamlform\YamlFormSubmissionAccessControlHandler.
 */

namespace Drupal\yamlform;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the YAML form submission entity type.
 *
 * @see \Drupal\yamlform\Entity\YamlFormSubmission.
 */
class YamlFormSubmissionAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  public function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $entity */
    $yamlform = $entity->getYamlForm();
    if ($yamlform->checkAccessRules($operation, $account, $entity)) {
      return AccessResult::allowed();
    }

    return parent::checkAccess($entity, $operation, $account);
  }

}
