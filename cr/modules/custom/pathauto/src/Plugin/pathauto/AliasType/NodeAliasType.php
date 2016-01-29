<?php

/**
 * @file
 * Contains \Drupal\pathauto\Plugin\AliasType\NodeAliasType.
 */

namespace Drupal\pathauto\Plugin\pathauto\AliasType;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\pathauto\AliasTypeBatchUpdateInterface;

/**
 * A pathauto alias type plugin for content entities.
 *
 * @AliasType(
 *   id = "node",
 *   label = @Translation("Content"),
 *   types = {"node"},
 *   provider = "node",
 * )
 */
class NodeAliasType extends EntityAliasTypeBase implements AliasTypeBatchUpdateInterface, ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getPatternDescription() {
    return $this->t('Default path pattern (applies to all content types with blank patterns below)');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array('default' => array('/content/[node:title]')) + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function batchUpdate(&$context) {
    if (!isset($context['sandbox']['current'])) {
      $context['sandbox']['count'] = 0;
      $context['sandbox']['current'] = 0;
    }

    $query = db_select('node', 'n');
    $query->leftJoin('url_alias', 'ua', "CONCAT('node/', n.nid) = ua.source");
    $query->addField('n', 'nid');
    $query->isNull('ua.source');
    $query->condition('n.nid', $context['sandbox']['current'], '>');
    $query->orderBy('n.nid');
    $query->addTag('pathauto_bulk_update');
    $query->addMetaData('entity', 'node');

    // Get the total amount of items to process.
    if (!isset($context['sandbox']['total'])) {
      $context['sandbox']['total'] = $query->countQuery()->execute()->fetchField();

      // If there are no nodes to update, the stop immediately.
      if (!$context['sandbox']['total']) {
        $context['finished'] = 1;
        return;
      }
    }

    $query->range(0, 25);
    $nids = $query->execute()->fetchCol();

    pathauto_node_update_alias_multiple($nids, 'bulkupdate');
    $context['sandbox']['count'] += count($nids);
    $context['sandbox']['current'] = max($nids);
    $context['message'] = t('Updated alias for node @nid.', array('@nid' => end($nids)));

    if ($context['sandbox']['count'] != $context['sandbox']['total']) {
      $context['finished'] = $context['sandbox']['count'] / $context['sandbox']['total'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getSourcePrefix() {
    return 'node/';
  }

}
