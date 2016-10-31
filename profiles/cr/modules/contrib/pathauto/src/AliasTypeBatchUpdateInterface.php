<?php

namespace Drupal\pathauto;

/**
 * Alias types that support batch updates.
 */
interface AliasTypeBatchUpdateInterface extends AliasTypeInterface {

  /**
   * Gets called to batch update all entries.
   * @param array $context
   *   Batch context.
   */
  public function batchUpdate(&$context);

}
