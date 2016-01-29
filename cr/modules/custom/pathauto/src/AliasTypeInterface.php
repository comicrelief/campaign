<?php

/**
 * @file
 * Contains Drupal\pathauto\AliasTypeInterface
 */

namespace Drupal\pathauto;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Provides an interface for pathauto alias types.
 */
interface AliasTypeInterface extends ConfigurablePluginInterface, PluginFormInterface {

  /**
   * Get the label.
   *
   * @return string
   *   The label.
   */
  public function getLabel();

  /**
   * Get the pattern description.
   *
   * @return string
   *   The pattern description.
   */
  public function getPatternDescription();

  /**
   * Get the patterns.
   *
   * @return string[]
   *   The array of patterns.
   */
  public function getPatterns();

  /**
   * Get the token types.
   *
   * @return string[]
   *   The token types.
   */
  public function getTokenTypes();

  /**
   * Returns the source prefix; used for bulk delete.
   *
   * @return string
   *   The source path prefix.
   */
  public function getSourcePrefix();

}
