<?php

namespace Drupal\block_visibility_groups\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;

/**
 * Provides a listing of Block Visibility Group entities.
 */
class BlockVisibilityGroupListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Block Visibility Group');
    $header['id'] = $this->t('Machine name');
    $header += parent::buildHeader();
    // $header['manage_blocks'] = $this->t('Manage Blocks');.
    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $this->getLabel($entity);
    $row['id'] = $entity->id();
    // You probably want a few more properties here...
    $row += parent::buildRow($entity);
    $url = Url::fromRoute(
      'block.admin_display',
      array(),
      ['query' => ['block_visibility_group' => $row['id']]]
    );
    /*$row['manage_blocks'] = array(
    '#type' => 'link',
    '#title' => 'Manage Blocks',
    // @todo Why does this crash?
    '#url' => $url,

    ); */
    $row['operations']['data']['#links']['manage_blocks'] = [
      'title' => $this->t('Manage Blocks'),
      'weight' => 80,
      'url' => $url,
    ];
    uasort($row['operations']['data']['#links'], '\Drupal\Component\Utility\SortArray::sortByWeightElement');
    return $row;
  }

}
