<?php

namespace Drupal\block_visibility_groups\Controller;

use Drupal\block_visibility_groups\Entity\BlockVisibilityGroup;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BlockVisibilityGroupController.
 *
 * @package Drupal\block_visibility_groups\Controller
 */
class BlockVisibilityGroupController extends ControllerBase {

  /**
   * Drupal\Core\Condition\ConditionManager definition.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConditionManager $plugin_manager_condition) {
    $this->conditionManager = $plugin_manager_condition;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.condition')
    );
  }

  /**
   * Index.
   *
   * @return string
   *   Return Hello string.
   */
  public function index($param_1, $param_2) {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: index with parameter(s): $param_1, $param_2'),
    ];
  }

  /**
   * Presents a list of conditions to add to the block_visibility_group entity.
   *
   * @param \Drupal\block_visibility_groups\Entity\BlockVisibilityGroup $block_visibility_group
   *   The block_visibility_group entity.
   *
   * @return array
   *   The condition selection page.
   */
  public function selectCondition(BlockVisibilityGroup $block_visibility_group, $redirect) {
    $build = [
      '#theme' => 'links',
      '#links' => [],
    ];
    $available_plugins = $this->conditionManager->getDefinitions();
    // @todo Should nesting Conditions be allowed
    unset($available_plugins['condition_group']);
    foreach ($available_plugins as $condition_id => $condition) {
      $build['#links'][$condition_id] = [
        'title' => $condition['label'],
        'url' => Url::fromRoute('block_visibility_groups.condition_add', [
          'block_visibility_group' => $block_visibility_group->id(),
          'condition_id' => $condition_id,
          'redirect' => $redirect,
        ]),
        'attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode([
            'width' => 'auto',
          ]),
        ],
      ];
    }
    return $build;
  }

}
