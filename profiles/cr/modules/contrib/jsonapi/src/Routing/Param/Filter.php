<?php

namespace Drupal\jsonapi\Routing\Param;
use Drupal\jsonapi\Error\SerializableHttpException;

/**
 * Class Filter.
 *
 * @package Drupal\jsonapi\Routing\Param
 */
class Filter extends JsonApiParamBase {

  /**
   * {@inheritdoc}
   */
  const KEY_NAME = 'filter';

  /**
   * Key in the filter[<key>] parameter for conditions.
   *
   * @var string
   */
  const CONDITION_KEY = 'condition';

  /**
   * Key in the filter[<key>] parameter for groups.
   *
   * @var string
   */
  const GROUP_KEY = 'group';

  /**
   * The field key in the filter condition: filter[lorem][condition][<field>].
   *
   * @var string
   */
  const FIELD_KEY = 'field';

  /**
   * The value key in the filter condition: filter[lorem][condition][<value>].
   *
   * @var string
   */
  const VALUE_KEY = 'value';

  /**
   * The operator key in the condition: filter[lorem][condition][<operator>].
   *
   * @var string
   */
  const OPERATOR_KEY = 'operator';

  /**
   * The conjunction key in the condition: filter[lorem][group][<conjunction>].
   *
   * @var string
   */
  const CONJUNCTION_KEY = 'conjunction';

  /**
   * {@inheritdoc}
   */
  protected function expand() {
    // We should always get an array for the filter.
    if (!is_array($this->original)) {
      throw new SerializableHttpException(400, 'Incorrect value passed to the filter parameter.');
    }

    $expanded = [];
    foreach ($this->original as $filter_index => $filter_item) {
      $expanded[$filter_index] = $this->expandItem($filter_index, $filter_item);
    }
    return $expanded;
  }

  /**
   * Expands a filter item in case a shortcut was used.
   *
   * Possible cases for the conditions:
   *   1. filter[uuid][value]=1234.
   *   2. filter[0][condition][field]=uuid&filter[0][condition][value]=1234.
   *   3. filter[uuid][condition][value]=1234.
   *   4. filter[uuid][value]=1234&filter[uuid][group]=my_group.
   *
   * @param string $filter_index
   *   The index.
   * @param array $filter_item
   *   The raw filter item.
   *
   * @return array
   *   The expanded filter item.
   */
  protected function expandItem($filter_index, array $filter_item) {
    if (isset($filter_item['value'])) {
      if (!isset($filter_item['field'])) {
        $filter_item['field'] = $filter_index;
      }
      $filter_item = [
        'condition' => $filter_item,
      ];

      if (!isset($filter_item['condition']['operator'])) {
        $filter_item['condition']['operator'] = '=';
      }
    }

    return $filter_item;
  }

}
