<?php

namespace Drupal\yamlform_node\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Defines the custom access control handler for the form node.
 */
class YamlFormNodeAccess {

  /**
   * Check whether the user can access a node's form.
   *
   * @param string $operation
   *   Operation being performed.
   * @param string $entity_access
   *   Entity access rule that needs to be checked.
   * @param \Drupal\node\NodeInterface $node
   *   A node.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  static public function checkYamlFormAccess($operation, $entity_access, NodeInterface $node, AccountInterface $account) {
    return self::checkAccess($operation, $entity_access, $node, NULL, $account);
  }

  /**
   * Check whether the user can access a node's form submission.
   *
   * @param string $operation
   *   Operation being performed.
   * @param string $entity_access
   *   Entity access rule that needs to be checked.
   * @param \Drupal\node\NodeInterface $node
   *   A node.
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A form submission.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  static public function checkYamlFormSubmissionAccess($operation, $entity_access, NodeInterface $node, YamlFormSubmissionInterface $yamlform_submission, AccountInterface $account) {
    return self::checkAccess($operation, $entity_access, $node, $yamlform_submission, $account);
  }

  /**
   * Check whether the user can access a node's form and/or submission.
   *
   * @param string $operation
   *   Operation being performed.
   * @param string $entity_access
   *   Entity access rule that needs to be checked.
   * @param \Drupal\node\NodeInterface $node
   *   A node.
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A form submission.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  static protected function checkAccess($operation, $entity_access, NodeInterface $node, YamlFormSubmissionInterface $yamlform_submission = NULL, AccountInterface $account = NULL) {
    // Check that the node has a valid form reference.
    if (!$node->hasField('yamlform') || !$node->yamlform->entity) {
      return AccessResult::forbidden();
    }

    // Check that the form submission was created via the form node.
    if ($yamlform_submission && $yamlform_submission->getSourceEntity() != $node) {
      return AccessResult::forbidden();
    }

    // Check the node operation.
    if ($operation && $node->access($operation, $account)) {
      return AccessResult::allowed();
    }

    // Check entity access.
    if ($entity_access) {
      // Check entity access for the form.
      if (strpos($entity_access, 'yamlform.') === 0
        && $node->yamlform->entity->access(str_replace('yamlform.', '', $entity_access), $account)) {
        return AccessResult::allowed();
      }
      // Check entity access for the form submission.
      if (strpos($entity_access, 'yamlform_submission.') === 0
        && $yamlform_submission->access(str_replace('yamlform_submission.', '', $entity_access), $account)) {
        return AccessResult::allowed();
      }
    }

    return AccessResult::forbidden();
  }

}
