<?php

/**
 * @file
 * Contains Drupal\pathauto\Entity\PathautoPattern.
 */

namespace Drupal\pathauto\Entity;

use Drupal\Core\Condition\ConditionPluginCollection;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\ContextInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;
use Drupal\pathauto\PathautoPatternInterface;

/**
 * Defines the Pathauto pattern entity.
 *
 * @ConfigEntityType(
 *   id = "pathauto_pattern",
 *   label = @Translation("Pathauto pattern"),
 *   handlers = {
 *     "list_builder" = "Drupal\pathauto\PathautoPatternListBuilder",
 *     "form" = {
 *       "default" = "Drupal\pathauto\Form\PatternEditForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "pattern",
 *   admin_permission = "administer pathauto",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "weight" = "weight"
 *   },
 *   lookup_keys = {
 *     "type",
 *   },
 *   links = {
 *     "collection" = "/admin/config/search/path/patterns",
 *     "edit-form" = "/admin/config/search/path/patterns/{pathauto_pattern}",
 *     "delete-form" = "/admin/config/search/path/patterns/{pathauto_pattern}/delete"
 *   }
 * )
 */
class PathautoPattern extends ConfigEntityBase implements PathautoPatternInterface {

  /**
   * The Pathauto pattern ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Pathauto pattern label.
   *
   * @var string
   */
  protected $label;

  /**
   * The pattern type.
   *
   * A string denoting the type of pathauto pattern this is. For a node path
   * this would be 'node', for users it would be 'user', and so on. This allows
   * for arbitrary non-entity patterns to be possible if applicable.
   *
   * @var string
   */
  protected $type;

  /**
   * @var \Drupal\Core\Plugin\DefaultSingleLazyPluginCollection
   */
  protected $aliasTypeCollection;

  /**
   * A tokenized string for alias generation.
   *
   * @var string
   */
  protected $pattern;

  /**
   * The plugin configuration for the selection criteria condition plugins.
   *
   * @var array
   */
  protected $selection_criteria = [];

  /**
   * The selection logic for this pattern entity (either 'and' or 'or').
   *
   * @var string
   */
  protected $selection_logic = 'and';

  /**
   * @var int
   */
  protected $weight = 0;

  /**
   * @var \Drupal\Core\Plugin\Context\ContextInterface[]
   */
  protected $contexts = [];

  /**
   * @var array
   */
  protected $context_definitions = [];

  /**
   * The plugin collection that holds the selection criteria condition plugins.
   *
   * @var \Drupal\Component\Plugin\LazyPluginCollection
   */
  protected $selectionConditionCollection;

  /**
   * {@inheritdoc}
   *
   * Not using core's default logic around ConditionPluginCollection since it
   * incorrectly assumes no condition will ever be applied twice.
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    $criteria = [];
    foreach ($this->getSelectionConditions() as $id => $condition) {
      $criteria[$id] = $condition->getConfiguration();
    }
    $this->selection_criteria = $criteria;

    /** @var \Drupal\Core\Plugin\Context\ContextInterface[] $contexts */
    $contexts = $this->getContexts();
    foreach ($this->getAliasType()->getContexts() as $plugin_context_id => $plugin_context) {
      unset($contexts[$plugin_context_id]);
    }
    $this->context_definitions = [];
    foreach ($contexts as $context_id => $context) {
      $this->context_definitions[] = [
        'id' => $context_id,
        'label' => $context->getContextDefinition()->getLabel()
      ];
    }

    // Invalidate the static caches.
    \Drupal::service('pathauto.generator')->resetCaches();
  }

  /**
   * {@inheritdoc}
   */
  public static function postLoad(EntityStorageInterface $storage, array &$entities) {
    /** @var \Drupal\ctools\TypedDataResolver $resolver */
    $resolver = \Drupal::service('ctools.typed_data.resolver');
    /** @var \Drupal\pathauto\Entity\PathautoPattern $entity */
    foreach ($entities as $entity) {
      foreach ($entity->getContextDefinitions() as $definition) {
        $context = $resolver->convertTokenToContext($definition['id'], $entity->getContexts());
        $new_definition = new ContextDefinition(
          $context->getContextDefinition()->getDataType(),
          $definition['label'],
          $context->getContextDefinition()->isRequired(),
          $context->getContextDefinition()->isMultiple(),
          $context->getContextDefinition()->getDescription(),
          $context->getContextDefinition()->getDefaultValue()
        );
        $new_context = new Context($new_definition, $context->hasContextValue() ? $context->getContextValue() : NULL);
        $entity->addContext($definition['id'], $new_context);
      }
    }
    parent::postLoad($storage, $entities);
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);
    // Invalidate the static caches.
    \Drupal::service('pathauto.generator')->resetCaches();
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();

    $this->calculatePluginDependencies($this->getAliasType());

    foreach ($this->getSelectionConditions() as $instance) {
      $this->calculatePluginDependencies($instance);
    }

    return $this->getDependencies();
  }

  /**
   * {@inheritdoc}
   */
  public function getPattern() {
    return $this->pattern;
  }

  /**
   * {@inheritdoc}
   */
  public function setPattern($pattern) {
    $this->pattern = $pattern;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->type;
  }

  /**
   * {@inheritdoc}
   */
  public function getAliasType() {
    if (!$this->aliasTypeCollection) {
      $this->aliasTypeCollection = new DefaultSingleLazyPluginCollection(\Drupal::service('plugin.manager.alias_type'), $this->getType(), ['default' => $this->getPattern()]);
    }
    return $this->aliasTypeCollection->get($this->getType());
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
    $this->weight = $weight;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasContext($token) {
    $contexts = $this->getAliasType()->getContexts();
    $contexts += $this->contexts;
    return !empty($contexts[$token]);
  }

  /**
   * {@inheritdoc}
   */
  public function getContext($token) {
    $contexts = $this->getAliasType()->getContexts();
    $contexts += $this->contexts;
    return $contexts[$token];
  }

  /**
   * {@inheritdoc}
   */
  public function getContexts() {
    $contexts = $this->getAliasType()->getContexts();
    $contexts += $this->contexts;
    return $contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function addContext($token, ContextInterface $context) {
    if (!$this->hasContext($token)) {
      $this->contexts[$token] = $context;
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function replaceContext($token, ContextInterface $context) {
    if ($this->hasContext($token)) {
      $this->contexts[$token] = $context;
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeContext($token) {
    if (isset($this->contexts[$token])) {
      unset($this->contexts[$token]);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContextDefinitions() {
    return $this->context_definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectionConditions() {
    if (!$this->selectionConditionCollection) {
      $this->selectionConditionCollection = new ConditionPluginCollection(\Drupal::service('plugin.manager.condition'), $this->get('selection_criteria'));
    }
    return $this->selectionConditionCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function addSelectionCondition(array $configuration) {
    $configuration['uuid'] = $this->uuidGenerator()->generate();
    $this->getSelectionConditions()->addInstanceId($configuration['uuid'], $configuration);
    return $configuration['uuid'];
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectionCondition($condition_id) {
    return $this->getSelectionConditions()->get($condition_id);
  }

  /**
   * {@inheritdoc}
   */
  public function removeSelectionCondition($condition_id) {
    $this->getSelectionConditions()->removeInstanceId($condition_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectionLogic() {
    return $this->selection_logic;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($object) {
    if ($this->getAliasType()->applies($object)) {
      $definitions = $this->getAliasType()->getContextDefinitions();
      if (count($definitions) > 1) {
        throw new \Exception("Alias types do not support more than one context.");
      }
      $keys = array_keys($definitions);
      // Set the context object on our Alias plugin before retrieving contexts.
      $this->getAliasType()->setContextValue($keys[0], $object);
      /** @var \Drupal\Core\Plugin\Context\ContextInterface[] $base_contexts */
      $base_contexts = $this->getAliasType()->getContexts();
      $contexts = [];
      foreach ($base_contexts as $context_id => $base_context) {
        $contexts[$context_id] = new Context($base_context->getContextDefinition(), $object);
      }
      /** @var \Drupal\ctools\TypedDataResolver $resolver */
      $resolver = \Drupal::service('ctools.typed_data.resolver');
      foreach ($this->getContexts() as $token => $context) {
        $contexts[$token] = $resolver->convertTokenToContext($token, $contexts);
      }
      /** @var \Drupal\Core\Plugin\Context\ContextHandler $context_handler */
      $context_handler = \Drupal::service('context.handler');
      $conditions = $this->getSelectionConditions();
      foreach ($conditions as $condition) {
        if ($condition instanceof ContextAwarePluginInterface) {
          $context_handler->applyContextMapping($condition, $contexts);
        }
        $result = $condition->execute();
        if ($this->getSelectionLogic() == 'and' && !$result) {
          return FALSE;
        }
        elseif ($this->getSelectionLogic() == 'or' && $result) {
          return TRUE;
        }
      }
      return TRUE;
    }
    return FALSE;
  }

}
