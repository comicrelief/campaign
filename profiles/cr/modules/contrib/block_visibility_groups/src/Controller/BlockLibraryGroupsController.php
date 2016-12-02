<?php

namespace Drupal\block_visibility_groups\Controller;

use Drupal\block\Controller\BlockLibraryController;
use Drupal\block_visibility_groups\Entity\BlockVisibilityGroup;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a list of block plugins to be added to the layout.
 */
class BlockLibraryGroupsController extends BlockLibraryController {

  /**
   * {@inheritdoc}
   */
  public function listBlocks(Request $request, $theme, BlockVisibilityGroup $block_visibility_group = NULL) {
    $list = parent::listBlocks($request, $theme);
    if ($block_visibility_group) {
      foreach ($list['blocks']['#rows'] as &$row) {
        $row['operations']['data']['#links']['add']['query']['block_visibility_group'] = $block_visibility_group->id();
      }
    }
    return $list;
  }

}
