<?php

namespace Drupal\block_visibility_groups\Form;

/**
 * Provides a form for editing an condition.
 */
class ConditionEditForm extends ConditionFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'block_visibility_group_manager_condition_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareCondition($condition_id) {
    // Load the condition directly from the block_visibility_group entity.
    return $this->block_visibility_group->getCondition($condition_id);
  }

  /**
   * {@inheritdoc}
   */
  protected function submitButtonText() {
    return $this->t('Update condition');
  }

  /**
   * {@inheritdoc}
   */
  protected function submitMessageText() {
    return $this->t('The %label condition has been updated.', ['%label' => $this->condition->getPluginDefinition()['label']]);
  }

}
