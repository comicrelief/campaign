<?php

/**
 * @file
 * Contains \Drupal\purge\Plugin\Purge\Queue\QueueBase.
 */

namespace Drupal\purge\Plugin\Purge\Queue;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\purge\Plugin\Purge\Queue\QueueInterface;

/**
 * Provides a ReliableQueueInterface compliant queue that holds queue items.
 */
abstract class QueueBase extends PluginBase implements QueueInterface {

  /**
   * The configured limit of items on selected data pages.
   *
   * @var int
   */
  protected $selectPageLimit = 15;

  /**
   * {@inheritdoc}
   */
  public function getPluginName() {
    return basename(str_replace('\\', '/', get_class($this)));
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function createItemMultiple(array $items) {
    $ids = [];

    // This implementation emulates multiple creation and is NOT efficient. It
    // exists for API reliability and invites derivatives to override it, for
    // example: by one multi-row database query.
    foreach ($items as $data) {
      if (($item = $this->createItem($data)) === FALSE) {
        return FALSE;
      }
      $ids[] = $item;
    }
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function claimItemMultiple($claims = 10, $lease_time = 3600) {
    $items = [];

    // This implementation emulates multiple item claiming and is NOT efficient,
    // but exists to provide a reliable API. Derivatives are invited to override
    // it, for example by one multi-row select database query.
    for ($i = 1; $i <= $claims; $i++) {
      if (($item = $this->claimItem($lease_time)) === FALSE) {
        break;
      }
      $items[] = $item;
    }
    return $items;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteItemMultiple(array $items) {

    // This implementation emulates multiple item deletion and is NOT efficient,
    // but exists to provide API reliability. Derivatives are invited to
    // override it, for example by one multi-row delete database query.
    foreach ($items as $item) {
      $this->deleteItem($item);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function releaseItemMultiple(array $items) {
    $failures = [];

    // This implementation emulates multiple item releases and is NOT efficient,
    // but exists to provide API reliability. Derivatives are invited to
    // override it, for example by a multi-row update database query.
    foreach ($items as $item) {
      if ($this->releaseItem($item) === FALSE) {
        $failures[] = $item;
      }
    }
    return $failures;
  }

  /**
   * {@inheritdoc}
   */
  public function selectPageLimit($set_limit_to = NULL) {
    if (is_int($set_limit_to)) {
      $this->selectPageLimit = $set_limit_to;
    }
    return $this->selectPageLimit;
  }

  /**
   * {@inheritdoc}
   */
  public function selectPageMax() {
    $max = ( (int)$this->numberOfItems() ) / $this->selectPageLimit();
    return intval(ceil($max));
  }

}
