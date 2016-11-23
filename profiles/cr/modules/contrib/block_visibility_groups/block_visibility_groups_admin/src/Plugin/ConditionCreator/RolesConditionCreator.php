<?php

namespace Drupal\block_visibility_groups_admin\Plugin\ConditionCreator;

use Drupal\block_visibility_groups_admin\Plugin\ConditionCreatorBase;

/**
 * A condition creator to be used in creating user role condition.
 *
 * @ConditionCreator(
 *   id = "roles",
 *   label = "Roles",
 *   condition_plugin = "user_role"
 * )
 */
class RolesConditionCreator extends ConditionCreatorBase {

  /**
   *
   */
  public function getNewConditionLabel() {
    return $this->t('Roles');
  }

  /**
   *
   */
  public function createConditionElements() {
    $elements['condition_config'] = [
      '#tree' => TRUE,
    ];
    // @todo Dynamically create condition for by call ConditionPluginBase::buildConfigurationForm?
    $elements['condition_config']['roles'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('When the user has the following roles'),
      '#options' => array_map('\Drupal\Component\Utility\Html::escape', user_role_names()),
      // '#description' => $this->t('If you select no roles, the condition will evaluate to TRUE for all users.'),.
    );
    return $elements;
  }

  /**
   *
   */
  public function itemSelected($condition_info) {
    $roles = $condition_info['condition_config']['roles'];
    return !empty(array_filter($roles));
  }

  /**
   *
   */
  public function createConditionConfig($plugin_info) {
    $config = parent::createConditionConfig($plugin_info);
    $config['roles'] = array_filter($config['roles']);
    // @todo Dynamically figure out context by loading connect plugin?
    $config['context_mapping'] = [
      'user' => '@user.current_user_context:current_user',
    ];
    return $config;
  }

}
