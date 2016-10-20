<?php

namespace Drupal\jsonapi\Query;

/**
 * Class GroupOption.
 *
 * A GroupOption can group other options before applying them to a query.
 *
 * @package \Drupal\jsonapi\Query\GroupOption
 */
class GroupOption implements QueryOptionInterface, QueryOptionTreeItemInterface {

  /**
   * A unique key.
   *
   * @var string
   */
  protected $id;

  /**
   * A unique key representing a parent condition group.
   *
   * @var string
   */
  protected $parentGroup;

  /**
   * An array of QueryOptions.
   *
   * @var \Drupal\jsonapi\Query\QueryOptionInterface[]
   */
  protected $childOptions;

  /**
   * An array of GroupOptions.
   *
   * @var \Drupal\jsonapi\Query\GroupOption[]
   */
  protected $childGroups;

  /**
   * Conjunction of the groups conditions.
   *
   * @var string
   */
  protected $conjunction;

  /**
   * Constructs a new GroupOption.
   *
   * @param string $id
   *   A unique string identifier for the option.
   * @param string $conjunction
   *   Conjunction of the groups conditions.
   * @param string $parent_group
   *   A unique key representing a parent condition group.
   */
  public function __construct($id, $conjunction = 'AND', $parent_group = NULL) {
    $this->id = $id;
    $this->conjunction = $conjunction;
    $this->parentGroup = $parent_group;
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function parentId() {
    return $this->parentGroup;
  }

  /**
   * {@inheritdoc}
   */
  public function insert($target_id, QueryOptionInterface $option) {
    $find_proper_id = function ($child_id, $group_option) use ($target_id) {
      if ($child_id) {
        return $child_id;
      };
      return $group_option->hasChild($target_id) ?
        $group_option->id() :
        NULL;
    };

    if ($this->id() == $target_id) {
      $prop = $option instanceof GroupOption ? 'childGroups' : 'childOptions';
      $this->{$prop}[$option->id()] = $option;
      return TRUE;
    }
    elseif ($proper_child = array_reduce($this->childGroups, $find_proper_id, NULL)) {
      return $this->childGroups[$proper_child]->insert($target_id, $option);
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function apply($query) {
    switch ($this->conjunction) {
      case 'OR':
        $group = $query->orConditionGroup();
        break;

      case 'AND':
      default:
        $group = $query->andConditionGroup();
        break;
    }

    if (!empty($this->childOptions)) {
      $group = array_reduce($this->childOptions, function ($group, $child) {
        return $child->apply($group);
      }, $group);
    }

    if (!empty($this->childGroups)) {
      $group = array_reduce($this->childGroups, function ($group, $child) {
        return $child->apply($group);
      }, $group);
    }

    return $query->condition($group);
  }

  /**
   * {@inheritdoc}
   */
  public function hasChild($id) {
    // Return FALSE if this node has no child.
    if (!isset($this->childOptions) || empty($this->childOptions)) {
      return FALSE;
    }

    // If any of the options have the specified id, return TRUE.
    if (in_array($id, array_keys($this->childOptions))) {
      return TRUE;
    }

    // If any child GroupOptions or their children have the id return TRUE.
    return array_reduce($this->groupOptions, function ($has_child, $group) use ($id) {
      // If we already know that we have the child, skip evaluation and return.
      if (!$has_child) {
        // Determine if this group or any of its children match the $id.
        $has_child = ($group->id() == $id || $group->hasChild($id));
      }
      return $has_child;
    }, FALSE);
  }

}
