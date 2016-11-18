<?php

namespace Drupal\context;

use Drupal\Core\Condition\ConditionInterface;
use Drupal\Core\Condition\ConditionPluginCollection;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\context\Plugin\ContextReactionPluginCollection;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;

interface ContextInterface extends ConfigEntityInterface, EntityWithPluginCollectionInterface {

  /**
   * The default value for a context that is not assigned to a group.
   */
  const CONTEXT_GROUP_NONE = NULL;

  /**
   * Get the ID of the context.
   *
   * @return string
   */
  public function id();

  /**
   * Get the machine name of the context.
   *
   * @return string
   */
  public function getName();

  /**
   * Set the machine name of the context.
   *
   * @param string $name
   *
   * @return $this
   */
  public function setName($name);

  /**
   * Get the context label.
   *
   * @return string
   */
  public function getLabel();

  /**
   * Set the context label.
   *
   * @param string $label
   *
   * @return $this
   */
  public function setLabel($label);

  /**
   * Get the context description.
   *
   * @return string
   */
  public function getDescription();

  /**
   * Set the context description.
   *
   * @param string $description
   *
   * @return $this
   */
  public function setDescription($description);

  /**
   * Get the group this context belongs to.
   *
   * @return null|string
   */
  public function getGroup();

  /**
   * Set the group this context should belong to.
   *
   * @param null|string $group
   *
   * @return $this
   */
  public function setGroup($group);

  /**
   * Get the weight for this context.
   *
   * @return int
   */
  public function getWeight();

  /**
   * Set the weight for this context.
   *
   * @param int $weight
   *   The weight to set for this context.
   *
   * @return $this
   */
  public function setWeight($weight);

  /**
   * If the context requires all conditions to validate.
   *
   * @return boolean
   */
  public function requiresAllConditions();

  /**
   * Set if all conditions should be required for this context to validate.
   *
   * @param bool $require
   *   If a condition is required or not.
   *
   * @return $this
   */
  public function setRequireAllConditions($require);

  /**
   * Get a list of all conditions.
   *
   * @return ConditionInterface[]|ConditionPluginCollection
   */
  public function getConditions();

  /**
   * Get a condition with the specified ID.
   *
   * @param string $condition_id
   *  The condition to get.
   *
   * @return \Drupal\Core\Condition\ConditionInterface
   */
  public function getCondition($condition_id);

  /**
   * Set the conditions.
   *
   * @param array $configuration
   *   The configuration for the condition plugin.
   *
   * @return string
   */
  public function addCondition(array $configuration);

  /**
   * Remove the specified condition.
   *
   * @param string $condition_id
   *   The id of the condition to remove.
   *
   * @return $this
   */
  public function removeCondition($condition_id);

  /**
   * Check to see if the context has the specified condition.
   *
   * @param string $condition_id
   *   The ID of the condition to check for.
   *
   * @return bool
   */
  public function hasCondition($condition_id);

  /**
   * Get a list of all the reactions.
   *
   * @return ContextReactionInterface[]|ContextReactionPluginCollection
   */
  public function getReactions();

  /**
   * Get a reaction with the specified ID.
   *
   * @param string $reaction_id
   *   The ID of the reaction to get.
   *
   * @return ContextReactionInterface
   */
  public function getReaction($reaction_id);

  /**
   * Add a context reaction.
   *
   * @param array $configuration
   *
   * @return string
   */
  public function addReaction(array $configuration);

  /**
   * Remove the specified reaction.
   *
   * @param string $reaction_id
   *   The id of the reaction to remove.
   *
   * @return $this
   */
  public function removeReaction($reaction_id);

  /**
   * Check to see if the context has the specified reaction.
   *
   * @param string $reaction_id
   *   The ID of the reaction to check for.
   *
   * @return bool
   */
  public function hasReaction($reaction_id);
}
