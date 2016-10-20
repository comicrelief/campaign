<?php

namespace Drupal\jsonapi\Query;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\jsonapi\Error\SerializableHttpException;
use Drupal\jsonapi\Routing\Param\OffsetPage;
use Drupal\jsonapi\Routing\Param\Filter;
use Drupal\jsonapi\Routing\Param\JsonApiParamInterface;
use Drupal\jsonapi\Context\CurrentContextInterface;
use Drupal\jsonapi\Context\FieldResolverInterface;
use Drupal\jsonapi\Routing\Param\Sort;

/**
 * Class QueryBuilder.
 *
 * @package Drupal\jsonapi\Query
 */
class QueryBuilder implements QueryBuilderInterface {

  /**
   * The entity type object that should be used for the query.
   */
  protected $entityType;

  /**
   * The options to build with which to build a query.
   */
  protected $options = [];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The JSON API current context service.
   *
   * @var \Drupal\jsonapi\Context\CurrentContextInterface
   */
  protected $currentContext;

  /**
   * The field resolver service.
   *
   * @var \Drupal\jsonapi\Context\FieldResolverInterface
   */
  protected $fieldResolver;

  /**
   * Contructs a new QueryBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   An instance of a QueryFactory.
   * @param \Drupal\jsonapi\Context\CurrentContextInterface $current_context
   *   An instance of the current context service.
   * @param \Drupal\jsonapi\Context\FieldResolverInterface $field_resolver
   *   The field resolver service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, CurrentContextInterface $current_context, FieldResolverInterface $field_resolver) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentContext = $current_context;
    $this->fieldResolver = $field_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public function newQuery(EntityTypeInterface $entity_type, array $params = []) {
    $this->entityType = $entity_type;

    $this->configureFromContext($params);

    $query = $this->entityTypeManager
      ->getStorage($this->entityType->id())
      ->getQuery()
      ->accessCheck(TRUE);

    // This applies each option from the option tree to the query before
    // returning it.
    $applied_query = array_reduce($this->options, function ($query, $option) {
      /* @var \Drupal\jsonapi\Query\QueryOptionInterface $option */
      return $option->apply($query);
    }, $query);

    return $applied_query ? $applied_query : $query;
  }

  /**
   * Configure the query from the current context and the provided parameters.
   *
   * To avoid using the global context so much use the passed in parameters
   * over the ones in the current context.
   *
   * @param \Drupal\jsonapi\Routing\Param\JsonApiParamInterface[] $params
   *   The JSON API parameters.
   */
  protected function configureFromContext(array $params = []) {
    // TODO: Explore the possibility to turn JsonApiParam into a plugin type.
    $param_keys = [Filter::KEY_NAME, Sort::KEY_NAME];
    foreach ($param_keys as $param_key) {
      if (isset($params[$param_key])) {
        $this->configureParam($param_key, $params[$param_key]);
      }
      elseif ($param = $this->currentContext->getJsonApiParameter($param_key)) {
        $this->configureParam($param_key, $param);
      }
    }
    // We always add a default pagination parameter.
    $pager = isset($params[OffsetPage::KEY_NAME]) ?
      $params[OffsetPage::KEY_NAME] :
      new OffsetPage([]);
    $this->configureParam(OffsetPage::KEY_NAME, $pager);
  }

  /**
   * Configure a parameter based on the type parameter type.
   *
   * @param string $type
   *   The parameter type.
   * @param \Drupal\jsonapi\Routing\Param\JsonApiParamInterface $param
   *   The parameter to configure.
   */
  protected function configureParam($type, JsonApiParamInterface $param) {
    switch ($type) {
      case Filter::KEY_NAME:
        $this->configureFilter($param);
        break;

      case Sort::KEY_NAME:
        $this->configureSort($param);
        break;

      case OffsetPage::KEY_NAME:
        $this->configurePager($param);
        break;
    }
  }

  /**
   * Configures the query builder from a Filter parameter.
   *
   * @param \Drupal\jsonapi\Routing\Param\JsonApiParamInterface $param
   *   A Filter parameter from which to configure this query builder.
   *
   * @todo The nested closures passing parameters by reference may not be ideal.
   */
  protected function configureFilter(JsonApiParamInterface $param) {
    $extracted = [];

    foreach ($param->get() as $filter_index => $filter) {
      foreach ($filter as $filter_type => $properties) {
        switch ($filter_type) {
          case Filter::CONDITION_KEY:
            $extracted[] = $this->newCondtionOption($filter_index, $properties);
            break;

          case Filter::GROUP_KEY:
            $extracted[] = $this->newGroupOption($filter_index, $properties);
            break;

          case Filter::EXISTS_KEY:
            break;

          default:
            throw new SerializableHttpException(
              400,
              sprintf('Invalid syntax in the filter parameter: %s.', $filter_index)
            );
        };
      }
    }

    $this->buildTree($extracted);
  }

  /**
   * Configures the query builder from a Sort parameter.
   *
   * @param \Drupal\jsonapi\Routing\Param\JsonApiParamInterface $param
   *   A Sort parameter from which to configure this query builder.
   */
  protected function configureSort(JsonApiParamInterface $param) {
    $extracted = [];
    foreach ($param->get() as $sort_index => $sort) {
      $extracted[] = $this->newSortOption(sprintf('sort_%s', $sort_index), $sort);
    }

    $this->buildTree($extracted);
  }

  /**
   * Configures the query builder from a Pager parameter.
   *
   * @param \Drupal\jsonapi\Routing\Param\JsonApiParamInterface $param
   *   A pager parameter from which to configure this query builder.
   */
  protected function configurePager(JsonApiParamInterface $param) {
    $this->buildTree([$this->newPagerOption($param->get())]);
  }

  /**
   * Returns a new ConditionOption.
   *
   * @param string $condition_id
   *   A unique id for the option.
   * @param array $properties
   *   The condition properties.
   *
   * @return \Drupal\jsonapi\Query\ConditionOption
   *   The condition object.
   */
  protected function newCondtionOption($condition_id, array $properties) {
    $langcode_key = $this->getLangcodeKey();
    $langcode = isset($properties[$langcode_key]) ? $properties[$langcode_key] : NULL;
    $group = isset($properties[Filter::GROUP_KEY]) ? $properties[Filter::GROUP_KEY] : NULL;
    return new ConditionOption(
      $condition_id,
      $this->fieldResolver->resolveInternal($properties[Filter::FIELD_KEY]),
      $properties[Filter::VALUE_KEY],
      $properties[Filter::OPERATOR_KEY],
      $langcode,
      $group
    );
  }

  /**
   * Returns a new GroupOption.
   *
   * @param string $identifier
   *   A unique id for the option.
   * @param array $properties
   *   The group properties.
   *
   * @return \Drupal\jsonapi\Query\GroupOption
   *   The group object.
   */
  protected function newGroupOption($identifier, array $properties) {
    $parent_group = isset($properties[Filter::GROUP_KEY]) ? $properties[Filter::GROUP_KEY] : NULL;
    return new GroupOption($identifier, $properties[Filter::CONJUNCTION_KEY], $parent_group);
  }

  /**
   * Returns a new SortOption.
   *
   * @param string $identifier
   *   A unique id for the option.
   * @param array $properties
   *   The sort properties.
   *
   * @return \Drupal\jsonapi\Query\SortOption
   *   The sort object.
   */
  protected function newSortOption($identifier, array $properties) {
    return new SortOption(
      $identifier,
      $this->fieldResolver->resolveInternal($properties[Sort::FIELD_KEY]),
      $properties[Sort::DIRECTION_KEY],
      $properties[Sort::LANGUAGE_KEY]
    );
  }

  /**
   * Returns a new SortOption.
   *
   * @param array $properties
   *   The pager properties.
   *
   * @return \Drupal\jsonapi\Query\SortOption
   *   The sort object.
   */
  protected function newPagerOption(array $properties) {
    // Add defaults to avoid unset warnings.
    $properties += [
      'size' => NULL,
      'offset' => 0,
    ];
    return new OffsetPagerOption($properties['size'], $properties['offset']);
  }

  /**
   * Builds a tree of QueryOptions.
   *
   * @param \Drupal\jsonapi\Query\QueryOptionInterface[] $options
   *   An array of QueryOptions.
   */
  protected function buildTree(array $options) {
    $remaining = $options;
    while (!empty($remaining)) {
      $insert = array_pop($remaining);
      if (method_exists($insert, 'parentId') && $parent_id = $insert->parentId()) {
        if (!$this->insert($parent_id, $insert)) {
          array_unshift($remaining, $insert);
        }
      }
      else {
        $this->options[$insert->id()] = $insert;
      }
    }
  }

  /**
   * Inserts a QueryOption into the appropriate child QueryOption.
   *
   * @param string $target_id
   *   Unique ID of the intended QueryOption parent.
   * @param \Drupal\jsonapi\Query\QueryOptionInterface $option
   *   The QueryOption to insert.
   *
   * @return bool
   *   Whether the option could be inserted or not.
   */
  protected function insert($target_id, QueryOptionInterface $option) {
    if (!empty($this->options)) {
      $find_target_child = function ($child, QueryOptionInterface $my_option) use ($target_id) {
        if ($child) {
          return $child;
        }
        if (
          $my_option->id() == $target_id ||
          (method_exists($my_option, 'hasChild') && $my_option->hasChild($target_id))
        ) {
          return $my_option->id();
        }
        return FALSE;
      };

      if ($appropriate_child = array_reduce($this->options, $find_target_child, NULL)) {
        return $this->options[$appropriate_child]->insert($target_id, $option);
      }
    }

    return FALSE;
  }

  /**
   * Get the language code key.
   *
   * @return string
   *   The key.
   */
  protected function getLangcodeKey() {
    $entity_type_id = $this->currentContext->getResourceConfig()
      ->getEntityTypeId();
    return $this->entityTypeManager
      ->getDefinition($entity_type_id)
      ->getKey('langcode');
  }

}
