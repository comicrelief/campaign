<?php

/**
 * @file
 * Contains \Drupal\pathauto\Plugin\AliasType\UserAliasType.
 */

namespace Drupal\pathauto\Plugin\pathauto\AliasType;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\pathauto\AliasTypeBatchUpdateInterface;

/**
 * A pathauto alias type plugin for user entities.
 *
 * @AliasType(
 *   id = "user",
 *   label = @Translation("User"),
 *   types = {"user"},
 *   provider = "user",
 * )
 */
class UserAliasType extends EntityAliasTypeBase implements AliasTypeBatchUpdateInterface, ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getPatternDescription() {
    return $this->t('Pattern for user account page paths');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array('default' => array('/users/[user:name]')) + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function batchUpdate(&$context) {
    if (!isset($context['sandbox']['current'])) {
      $context['sandbox']['count'] = 0;
      $context['sandbox']['current'] = 0;
    }

    $query = db_select('users', 'u');
    $query->leftJoin('url_alias', 'ua', "CONCAT('/user/', u.uid) = ua.source");
    $query->addField('u', 'uid');
    $query->isNull('ua.source');
    $query->condition('u.uid', $context['sandbox']['current'], '>');
    $query->orderBy('u.uid');
    $query->addTag('pathauto_bulk_update');
    $query->addMetaData('entity', 'user');

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
    $uids = $query->execute()->fetchCol();

    pathauto_user_update_alias_multiple($uids, 'bulkupdate', array());
    $context['sandbox']['count'] += count($uids);
    $context['sandbox']['current'] = max($uids);
    $context['message'] = t('Updated alias for user @uid.', array('@uid' => end($uids)));

    if ($context['sandbox']['count'] != $context['sandbox']['total']) {
      $context['finished'] = $context['sandbox']['count'] / $context['sandbox']['total'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getSourcePrefix() {
    return '/user/';
  }

}
