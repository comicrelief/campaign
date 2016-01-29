<?php

/**
 * @file
 * Contains \Drupal\pathauto\Plugin\AliasType\ForumAliasType.
 */

namespace Drupal\pathauto\Plugin\pathauto\AliasType;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\pathauto\AliasTypeBatchUpdateInterface;

/**
 * A pathauto alias type plugin for forum terms.
 *
 * @AliasType(
 *   id = "forum",
 *   label = @Translation("Forum"),
 *   types = {"term"},
 *   provider = "forum",
 * )
 */
class ForumAliasType extends EntityAliasTypeBase implements AliasTypeBatchUpdateInterface, ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getPatternDescription() {
    return $this->t('Pattern for forums and forum containers');
  }

  /**
   * {@inheritdoc}
   */
  public function getPatterns() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array('default' => array('/[term:vocabulary]/[term:name]')) + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function batchUpdate(&$context) {
    if (!isset($context['sandbox']['current'])) {
      $context['sandbox']['count'] = 0;
      $context['sandbox']['current'] = 0;
    }

    $query = db_select('taxonomy_term_data', 'td');
    $query->leftJoin('url_alias', 'ua', "CONCAT('forum/', td.tid) = ua.source");
    $query->addField('td', 'tid');
    $query->isNull('ua.source');
    $query->condition('td.tid', $context['sandbox']['current'], '>');
    $query->condition('td.vid', 'forums');
    $query->orderBy('td.tid');
    $query->addTag('pathauto_bulk_update');
    $query->addMetaData('entity', 'taxonomy_term');

    // Get the total amount of items to process.
    if (!isset($context['sandbox']['total'])) {
      $context['sandbox']['total'] = $query->countQuery()
        ->execute()
        ->fetchField();

      // If there are no nodes to update, the stop immediately.
      if (!$context['sandbox']['total']) {
        $context['finished'] = 1;
        return;
      }
    }

    $query->range(0, 25);
    $tids = $query->execute()->fetchCol();

    pathauto_taxonomy_term_update_alias_multiple($tids, 'bulkupdate');
    $context['sandbox']['count'] += count($tids);
    $context['sandbox']['current'] = max($tids);
    $context['message'] = t('Updated alias for forum @tid.', array('@tid' => end($tids)));

    if ($context['sandbox']['count'] != $context['sandbox']['total']) {
      $context['finished'] = $context['sandbox']['count'] / $context['sandbox']['total'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getSourcePrefix() {
    return 'forum/';
  }

}
