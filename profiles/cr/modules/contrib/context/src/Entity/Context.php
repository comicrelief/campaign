<?php

namespace Drupal\context\Entity;

use Drupal;
use InvalidArgumentException;
use Drupal\context\ContextInterface;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Condition\ConditionPluginCollection;
use Drupal\context\Plugin\ContextReactionPluginCollection;

/**
 * Defines the Context entity.
 *
 * @ConfigEntityType(
 *   id = "context",
 *   label = @Translation("Context"),
 *   handlers = {
 *     "access" = "Drupal\context\Entity\ContextAccess",
 *     "list_builder" = "Drupal\context_ui\ContextListBuilder",
 *     "form" = {
 *       "add" = "Drupal\context_ui\Form\ContextAddForm",
 *       "edit" = "Drupal\context_ui\Form\ContextEditForm",
 *       "delete" = "Drupal\context_ui\Form\ContextDeleteForm",
 *     }
 *   },
 *   links = {
 *     "edit-form" = "/admin/structure/context/{context}",
 *     "delete-form" = "/admin/structure/context/{context}/delete",
 *     "collection" = "/admin/structure/context",
 *   },
 *   admin_permission = "administer contexts",
 *   entity_keys = {
 *     "id" = "name",
 *     "label" = "label",
 *   },
 *   config_export = {
 *     "name",
 *     "label",
 *     "group",
 *     "description",
 *     "requireAllConditions",
 *     "disabled",
 *     "conditions",
 *     "reactions",
 *     "weight",
 *   }
 * )
 */
class Context extends ConfigEntityBase implements ContextInterface {

  /**
   * The machine name of the context.
   *
   * @var string
   */
  protected $name;

  /**
   * The label of the context.
   *
   * @var string
   */
  protected $label;

  /**
   * A description for this context.
   *
   * @var string
   */
  protected $description = '';

  /**
   * The group this context belongs to.
   *
   * @var string|null
   */
  protected $group = self::CONTEXT_GROUP_NONE;

  /**
   * If all conditions must validate for this context.
   *
   * @var bool
   */
  protected $requireAllConditions = FALSE;

  /**
   * The context conditions as a collection.
   *
   * @var ConditionPluginCollection
   */
  protected $conditionsCollection;

  /**
   * The context reactions as a collection.
   *
   * @var ContextReactionPluginCollection
   */
  protected $reactionsCollection;

  /**
   * A list of conditions this context should react to.
   *
   * @var array
   */
  protected $conditions = [];

  /**
   * A list of reactions that should be taken when conditions match.
   *
   * @var array
   */
  protected $reactions = [];

  /**
   * If the context is disabled or not.
   *
   * @var bool
   */
  protected $disabled = FALSE;

  /**
   * The weight for this context.
   *
   * @var int
   */
  protected $weight = 0;

  /**
   * Returns the ID of the context. The ID is the unique machine name of the
   * context.
   */
  public function id() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {

    if (!is_string($name)) {
      throw new InvalidArgumentException('The context name must be a string.');
    }

    $this->name = $name;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function setLabel($label) {

    if (!is_string($label)) {
      throw new InvalidArgumentException('The context label must be a string.');
    }

    $this->label = $label;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {

    if (!is_string($description)) {
      throw new InvalidArgumentException('The context description must be a string.');
    }

    $this->description = $description;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getGroup() {
    return $this->group;
  }

  /**
   * {@inheritdoc}
   */
  public function setGroup($group) {
    $this->group = (is_string($group) && !empty($group)) ? $group : self::CONTEXT_GROUP_NONE;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->weight = (int) $weight;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function requiresAllConditions() {
    return $this->requireAllConditions;
  }

  /**
   * {@inheritdoc}
   */
  public function setRequireAllConditions($require) {
    $this->requireAllConditions = (bool) $require;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getConditions() {
    if (!$this->conditionsCollection) {
      $conditionManager =  Drupal::service('plugin.manager.condition');
      $this->conditionsCollection = new ConditionPluginCollection($conditionManager, $this->conditions);
    }

    return $this->conditionsCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getCondition($condition_id) {
    return $this->getConditions()->get($condition_id);
  }

  /**
   * {@inheritdoc}
   */
  public function addCondition(array $configuration) {
    // Add an UUID to the condition to make sure the configuration is saved
    // since the configuration export from the conditions collection wont
    // export configuration that has not been "configured".
    $configuration['uuid'] = $this->uuidGenerator()->generate();

    $this->getConditions()->addInstanceId($configuration['id'], $configuration);

    return $configuration['id'];
  }

  /**
   * {@inheritdoc}
   */
  public function removeCondition($condition_id) {
    $this->getConditions()->removeInstanceId($condition_id);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasCondition($condition_id) {
    return $this->getConditions()->has($condition_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getReactions() {
    if (!$this->reactionsCollection) {
      $reactionManager = Drupal::service('plugin.manager.context_reaction');
      $this->reactionsCollection = new ContextReactionPluginCollection($reactionManager, $this->reactions);
    }

    return $this->reactionsCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getReaction($reaction_id) {
    return $this->getReactions()->get($reaction_id);
  }

  /**
   * {@inheritdoc}
   */
  public function addReaction(array $configuration) {
    // Add an UUID to the condition to make sure the configuration is saved
    // since the configuration export from the conditions collection wont
    // export configuration that has not been "configured".
    $configuration['uuid'] = $this->uuidGenerator()->generate();

    $this->getReactions()->addInstanceId($configuration['id'], $configuration);

    return $configuration['id'];
  }

  /**
   * {@inheritdoc}
   */
  public function removeReaction($reaction_id) {
    $this->getReactions()->removeInstanceId($reaction_id);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasReaction($reaction_id) {
    return $this->getReactions()->has($reaction_id);
  }

  /**
   * Gets the plugin collections used by this entity.
   *
   * @return \Drupal\Component\Plugin\LazyPluginCollection[]
   *   An array of plugin collections, keyed by the property name they use to
   *   store their configuration.
   */
  public function getPluginCollections() {
    return [
      'reactions' => $this->getReactions(),
      'conditions' => $this->getConditions(),
    ];
  }
}
