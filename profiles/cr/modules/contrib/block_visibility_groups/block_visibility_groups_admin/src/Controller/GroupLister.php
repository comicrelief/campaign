<?php

namespace Drupal\block_visibility_groups_admin\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GroupLister.
 *
 * @package Drupal\block_visibility_groups_admin\Controller
 */
class GroupLister extends ControllerBase {

  /**
   * Drupal\block_visibility_groups_admin\GroupInfo definition.
   *
   * @var \Drupal\block_visibility_groups_admin\GroupInfo
   */
  protected $group_info;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManager $entityTypeManager) {
    $this->storage = $entityTypeManager->getStorage('block_visibility_group');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * @param $active_group_ids
   *
   * @return array
   */
  public function activeList($active_group_ids) {
    $active_group_ids = explode(',', $active_group_ids);
    /** @var \Drupal\block_visibility_groups\Entity\BlockVisibilityGroup[] $groups */
    $groups = $this->storage->loadMultiple($active_group_ids);

    $edit_links = [];
    foreach ($groups as $group) {
      $edit_links[] = [
        '#type' => 'container',

        'edit' => [
          '#type' => 'link',
          '#title' => $group->label(),
          '#url' => $group->urlInfo('edit-form'),
          '#suffix' => ' - ',
        ],
        'manage' => [
          '#type' => 'link',
          '#title' => t('Manage Blocks'),
          '#url' => Url::fromRoute('block.admin_display_theme', [
            'theme' => \Drupal::theme()->getActiveTheme()->getName(),
          ],
            [
              'query' => ['block_visibility_group' => $group->id()],
            ]
          ),
        ],

      ];
    }
    return $edit_links;
  }

}
