<?php

/**
 * @file
 * Contains Drupal\pathauto\PathautoPatternInterface.
 */

namespace Drupal\pathauto;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Plugin\Context\ContextInterface;

/**
 * Provides an interface for defining Pathauto pattern entities.
 */
interface PathautoPatternInterface extends ConfigEntityInterface {

  /**
   * Get the tokenized pattern used during alias generation.
   *
   * @return string
   */
  public function getPattern();

  /**
   * Set the tokenized pattern to use during alias generation.
   *
   * @param string $pattern
   *
   * @return $this
   */
  public function setPattern($pattern);

  /**
   * Gets the type of this pattern.
   *
   * @return string
   */
  public function getType();

  /**
   * @return \Drupal\pathauto\AliasTypeInterface
   */
  public function getAliasType();

  /**
   * Gets the weight of this pattern (compared to other patterns of this type).
   *
   * @return int
   */
  public function getWeight();

  /**
   * Sets the weight of this pattern (compared to other patterns of this type).
   *
   * @param int $weight
   *   The weight of the variant.
   *
   * @return $this
   */
  public function setWeight($weight);

  /**
   * @return bool
   */
  public function hasContext($token);

  /**
   * @return \Drupal\Core\Plugin\Context\ContextInterface
   */
  public function getContext($token);

  /**
   * @return \Drupal\Core\Plugin\Context\ContextInterface[]
   */
  public function getContexts();

  /**
   * @param string $token
   * @param \Drupal\Core\Plugin\Context\ContextInterface $context
   *
   * @return $this
   */
  public function addContext($token, ContextInterface $context);

  /**
   * @param string $token
   * @param \Drupal\Core\Plugin\Context\ContextInterface $context
   *
   * @return $this
   */
  public function replaceContext($token, ContextInterface $context);

  /**
   * @param string $token
   *
   * @return $this
   */
  public function removeContext($token);

  public function getContextDefinitions();

  /**
   * Gets the selection condition collection.
   *
   * @return \Drupal\Core\Condition\ConditionInterface[]|\Drupal\Core\Condition\ConditionPluginCollection
   */
  public function getSelectionConditions();

  /**
   * Adds selection criteria.
   *
   * @param array $configuration
   *   Configuration of the selection criteria.
   *
   * @return string
   *   The condition id of the new criteria.
   */
  public function addSelectionCondition(array $configuration);

  /**
   * Gets selection criteria by condition id.
   *
   * @param string $condition_id
   *   The id of the condition.
   *
   * @return \Drupal\Core\Condition\ConditionInterface
   */
  public function getSelectionCondition($condition_id);

  /**
   * Removes selection criteria by condition id.
   *
   * @param string $condition_id
   *   The id of the condition.
   *
   * @return $this
   */
  public function removeSelectionCondition($condition_id);

  /**
   * Gets the selection logic used by the criteria (ie. "and" or "or").
   *
   * @return string
   *   Either "and" or "or"; represents how the selection criteria are combined.
   */
  public function getSelectionLogic();

  /**
   * Determines if this pattern can apply a given object.
   *
   * @param $object
   *   The object used to determine if this plugin can apply.
   *
   * @return bool
   */
  public function applies($object);

}
