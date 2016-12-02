<?php

namespace Drupal\block_visibility_groups_admin;

use Drupal\block_visibility_groups\GroupEvaluator;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Executable\ExecutableManagerInterface;

/**
 * Class GroupInfo.
 *
 * @package Drupal\block_visibility_groups_admin
 */
class GroupInfo implements GroupInfoInterface {

  /**
   * Drupal\block_visibility_groups\GroupEvaluator definition.
   *
   * @var \Drupal\block_visibility_groups\GroupEvaluator
   */
  protected $block_visibility_groups_group_evaluator;


  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $group_storage;

  /**
   * @var \Drupal\Core\Executable\ExecutableManagerInterface
   */
  protected $condition_manager;

  /**
   * Constructor.
   *
   * @param \Drupal\block_visibility_groups\GroupEvaluator $block_visibility_groups_group_evaluator
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   * @param \Drupal\Core\Executable\ExecutableManagerInterface $condition_manager
   */
  public function __construct(GroupEvaluator $block_visibility_groups_group_evaluator, EntityTypeManagerInterface $entity_manager, ExecutableManagerInterface $condition_manager) {
    $this->block_visibility_groups_group_evaluator = $block_visibility_groups_group_evaluator;
    $this->group_storage = $entity_manager->getStorage('block_visibility_group');
    $this->condition_manager = $condition_manager;
  }

  /**
   * Get all active groups.
   *
   * @return \Drupal\block_visibility_groups\Entity\BlockVisibilityGroup[]
   */
  public function getActiveGroups() {
    $active_groups = [];
    /** @var BlockVisibilityGroup $group */
    foreach ($this->group_storage->loadMultiple() as $id => $group) {
      /** @var \Drupal\block_visibility_groups\Plugin\Condition\ConditionGroup $condition ; */
      $condition = $this->condition_manager->createInstance('condition_group', ['block_visibility_group' => $id]);
      if ($condition->evaluate()) {
        $active_groups[$id] = $group;
      }
    }
    return $active_groups;
  }

}
