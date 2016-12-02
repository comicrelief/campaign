<?php

namespace Drupal\block_visibility_groups;

use Drupal\block_visibility_groups\Entity\BlockVisibilityGroup;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Url;

/**
 *
 */
trait ConditionsSetFormTrait {

  /**
   * @param array $form
   * @param $block_visibility_group
   *
   * @return array
   */
  protected function createConditionsSet(array $form, BlockVisibilityGroup $block_visibility_group, $redirect = 'edit') {
    $attributes = [
      'class' => ['use-ajax'],
      'data-dialog-type' => 'modal',
      'data-dialog-options' => Json::encode([
        'width' => 'auto',
      ]),
    ];
    $add_button_attributes = NestedArray::mergeDeep($attributes, [
      'class' => [
        'button',
        'button--small',
        'button-action',
        'form-item',
      ],
    ]);
    $form['conditions_section'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Conditions'),
      '#open' => TRUE,
    ];

    $form['conditions_section']['add_condition'] = [
      '#type' => 'link',
      '#title' => $this->t('Add new condition'),
      '#url' => Url::fromRoute('block_visibility_groups.condition_select', [
        'block_visibility_group' => $block_visibility_group->id(),
        'redirect' => $redirect,
      ]),
      '#attributes' => $add_button_attributes,
      '#attached' => [
        'library' => [
          'core/drupal.ajax',
        ],
      ],
    ];
    if ($conditions = $block_visibility_group->getConditions()) {
      $form['conditions_section']['conditions'] = [
        '#type' => 'table',
        '#header' => [
          $this->t('Label'),
          $this->t('Description'),
          $this->t('Operations'),
        ],
        '#empty' => $this->t('There are no conditions.'),
      ];

      foreach ($conditions as $condition_id => $condition) {
        $row = [];
        $row['label']['#markup'] = $condition->getPluginDefinition()['label'];
        $row['description']['#markup'] = $condition->summary();
        $operations = [];
        $operations['edit'] = [
          'title' => $this->t('Edit'),
          'url' => Url::fromRoute('block_visibility_groups.condition_edit', [
            'block_visibility_group' => $block_visibility_group->id(),
            'condition_id' => $condition_id,
            'redirect' => $redirect,
          ]),
          'attributes' => $attributes,
        ];
        $operations['delete'] = [
          'title' => $this->t('Delete'),
          'url' => Url::fromRoute('block_visibility_groups.condition_delete', [
            'block_visibility_group' => $block_visibility_group->id(),
            'condition_id' => $condition_id,
            'redirect' => $redirect,
          ]),
          'attributes' => $attributes,
        ];
        $row['operations'] = [
          '#type' => 'operations',
          '#links' => $operations,
        ];
        $form['conditions_section']['conditions'][$condition_id] = $row;
      }
    }
    return $form['conditions_section'];
  }

}
