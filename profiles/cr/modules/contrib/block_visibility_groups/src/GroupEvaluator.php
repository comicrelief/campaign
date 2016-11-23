<?php

namespace Drupal\block_visibility_groups;

use Drupal\block_visibility_groups\Entity\BlockVisibilityGroup;
use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Core\Condition\ConditionAccessResolverTrait;
use Drupal\Core\Condition\ConditionPluginCollection;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;

/**
 * Class ConditionEvaluator.
 *
 * @package Drupal\block_visibility_groups
 */
class GroupEvaluator implements GroupEvaluatorInterface {

  use ConditionAccessResolverTrait;

  /**
   * The plugin context handler.
   *
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface
   */
  protected $contextHandler;

  /**
   * The context manager service.
   *
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   */
  protected $contextRepository;
  /**
   * @var array $group_evaluations ;
   */
  protected $group_evaluations = [];

  /**
   * Constructor.
   */
  public function __construct(ContextHandlerInterface $context_handler, ContextRepositoryInterface $context_repository) {

    $this->contextRepository = $context_repository;
    $this->contextHandler = $context_handler;
  }

  /**
   * @param \Drupal\block_visibility_groups\Entity\BlockVisibilityGroup $block_visibility_group
   *
   * @return boolean
   */
  public function evaluateGroup(BlockVisibilityGroup $block_visibility_group) {
    $group_id = $block_visibility_group->id();
    if (!isset($this->group_evaluations[$group_id])) {
      /** @var ConditionPluginCollection $conditions */
      $conditions = $block_visibility_group->getConditions();
      if (empty($conditions)) {
        // If no conditions then always true.
        return TRUE;
      }
      $logic = $block_visibility_group->getLogic();
      if ($this->applyContexts($conditions, $logic)) {
        $this->group_evaluations[$group_id] = $this->resolveConditions($conditions, $logic);
      }
      else {
        $this->group_evaluations[$group_id] = FALSE;
      }
    }
    return $this->group_evaluations[$group_id];
  }

  /**
   *
   */
  protected function applyContexts(ConditionPluginCollection &$conditions, $logic) {
    $have_1_testable_condition = FALSE;
    foreach ($conditions as $id => $condition) {
      if ($condition instanceof ContextAwarePluginInterface) {
        try {
          $contexts = $this->contextRepository->getRuntimeContexts(array_values($condition->getContextMapping()));
          $this->contextHandler->applyContextMapping($condition, $contexts);
          $have_1_testable_condition = TRUE;
        }
        catch (ContextException $e) {
          if ($logic == 'and') {
            // Logic is all and found condition with contextException.
            return FALSE;
          }
          $conditions->removeInstanceId($id);

        }

      }
      else {
        $have_1_testable_condition = TRUE;
      }
    }
    if ($logic == 'or' && !$have_1_testable_condition) {
      return FALSE;
    }
    return TRUE;
  }

}
