<?php

namespace Drupal\jsonapi\Routing\Param;
use Drupal\jsonapi\Error\SerializableHttpException;

/**
 * Class Sort.
 *
 * @package Drupal\jsonapi\Routing\Param
 */
class Sort extends JsonApiParamBase {

  /**
   * {@inheritdoc}
   */
  const KEY_NAME = 'sort';

  /**
   * The field key in the sort parameter: sort[lorem][<field>].
   *
   * @var string
   */
  const FIELD_KEY = 'field';

  /**
   * The direction key in the sort parameter: sort[lorem][<direction>].
   *
   * @var string
   */
  const DIRECTION_KEY = 'direction';

  /**
   * The langcode key in the sort parameter: sort[lorem][<langcode>].
   *
   * @var string
   */
  const LANGUAGE_KEY = 'langcode';

  /**
   * The conjunction key in the condition: filter[lorem][group][<conjunction>].
   *
   * @var string
   */

  /**
   * {@inheritdoc}
   */
  protected function expand() {
    $sort = $this->original;

    if (empty($sort)) {
      throw new SerializableHttpException(400, 'You need to provide a value for the sort parameter.');
    }

    // Expand a JSON API compliant sort into a more expressive sort parameter.
    if (is_string($sort)) {
      $sort = $this->expandFieldString($sort);
    }

    // Expand any defaults into the sort array.
    $expanded = [];
    foreach ($sort as $sort_index => $sort_item) {
      $expanded[$sort_index] = $this->expandItem($sort_index, $sort_item);
    }

    return $expanded;
  }

  /**
   * Expands a simple string sort into a more expressive sort that we can use.
   *
   * @param string $fields
   *   The comma separated list of fields to expand into an array.
   *
   * @return array
   *   The expanded sort.
   */
  protected function expandFieldString($fields) {
    return array_map(function ($field) {
      $sort = [];

      if ($field[0] == '-') {
        $sort[static::DIRECTION_KEY] = 'DESC';
        $sort[static::FIELD_KEY] = substr($field, 1);
      }
      else {
        $sort[static::DIRECTION_KEY] = 'ASC';
        $sort[static::FIELD_KEY] = $field;
      }

      return $sort;
    }, explode(',', $fields));
  }

  /**
   * Expands a sort item in case a shortcut was used.
   *
   * @param string $sort_index
   *   Unique identifier for the sort parameter being expanded.
   * @param array $sort_item
   *   The raw sort item.
   *
   * @return array
   *   The expanded sort item.
   */
  protected function expandItem($sort_index, array $sort_item) {
    $defaults = [
      static::DIRECTION_KEY => 'ASC',
      static::LANGUAGE_KEY => NULL,
    ];

    if (!isset($sort_item[static::FIELD_KEY])) {
      throw new SerializableHttpException(400, 'You need to provide a field name for the sort parameter.');
    }

    $expected_keys = [
      static::FIELD_KEY,
      static::DIRECTION_KEY,
      static::LANGUAGE_KEY,
    ];

    $expanded = array_merge($defaults, $sort_item);

    // Verify correct sort keys.
    if (count(array_diff($expected_keys, array_keys($expanded))) > 0) {
      throw new SerializableHttpException(400, 'You have provided an invalid set of sort keys.');
    }

    return $expanded;
  }

}
