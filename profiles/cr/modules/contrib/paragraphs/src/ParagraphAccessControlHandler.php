<?php

/**
 * @file
 * Contains Drupal\account/ParagraphAccessController.
 */

namespace Drupal\paragraphs;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the paragraphs entity.
 *
 * @see \Drupal\paragraphs\Entity\Paragraph.
 */
class ParagraphAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    // Allowed when the operation is not view or the status is true.
    return AccessResult::allowedIf($operation != 'view' || $entity->status->value);
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    // Allowed when nobody implements.
    return AccessResult::allowed();
  }

}
