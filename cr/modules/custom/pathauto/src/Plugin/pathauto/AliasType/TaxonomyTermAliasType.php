<?php

/**
 * @file
 * Contains \Drupal\pathauto\Plugin\AliasType\TaxonomyTermAliasType.
 */

namespace Drupal\pathauto\Plugin\pathauto\AliasType;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\pathauto\AliasTypeBatchUpdateInterface;

/**
 * A pathauto alias type plugin for taxonomy term entities.
 *
 * @AliasType(
 *   id = "taxonomy_term",
 *   label = @Translation("Taxonomy term paths"),
 *   types = {"term"},
 *   provider = "taxonomy",
 * )
 */
class TaxonomyTermAliasType extends EntityAliasTypeBase implements AliasTypeBatchUpdateInterface, ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getPatternDescription() {
    return $this->t('Default path pattern (applies to all vocabularies with blank patterns below)');
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
    $query->leftJoin('url_alias', 'ua', "CONCAT('taxonomy/term/', td.tid) = ua.source");
    $query->addField('td', 'tid');
    $query->isNull('ua.source');
    $query->condition('td.tid', $context['sandbox']['current'], '>');
    // Exclude the forums terms.
    if ($forum_vid = 'forums') {
      $query->condition('td.vid', $forum_vid, '<>');
    }
    $query->orderBy('td.tid');
    $query->addTag('pathauto_bulk_update');
    $query->addMetaData('entity', 'taxonomy_term');

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
    $tids = $query->execute()->fetchCol();

    pathauto_taxonomy_term_update_alias_multiple($tids, 'bulkupdate');
    $context['sandbox']['count'] += count($tids);
    $context['sandbox']['current'] = max($tids);
    $context['message'] = t('Updated alias for term @tid.', array('@tid' => end($tids)));

    if ($context['sandbox']['count'] != $context['sandbox']['total']) {
      $context['finished'] = $context['sandbox']['count'] / $context['sandbox']['total'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getSourcePrefix() {
    return 'taxonomy/term/';
  }

}
