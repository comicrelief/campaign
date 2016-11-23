<?php

namespace Drupal\block_visibility_groups\Form;

use Drupal\block_visibility_groups\BlockVisibilityLister;
use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Builds the form to delete Block Visibility Group entities.
 */
class BlockVisibilityGroupDeleteForm extends EntityConfirmFormBase {
  use BlockVisibilityLister;

  const UNSET_BLOCKS = 'UNSET-BLOCKS';
  const DELETE_BLOCKS = 'DELETE-BLOCKS';

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete %name?', array('%name' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.block_visibility_group.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Handle current blocks according to user's selection.
    if ($blocks = $this->getBlocksForGroup()) {
      $blocks_op = $form_state->getValue('blocks_op');
      switch ($blocks_op) {
        case static::DELETE_BLOCKS:
          $this->blockStorage()->delete($blocks);
          break;

        case static::UNSET_BLOCKS:
          $this->setBlocksGroup($blocks);
          break;

        default:
          $this->setBlocksGroup($blocks, $blocks_op);
      }
    }
    $this->entity->delete();

    drupal_set_message(
      $this->t('Deleted @type:  @label.',
        [
          '@type' => $this->entity->getEntityType()->getLabel(),
          '@label' => $this->entity->label(),
        ]
      )
    );

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

  /**
   * Set the visibility group for blocks.
   *
   * @param array $blocks
   *   The blocks.
   * @param string $group_id
   *   The group id.
   */
  public function setBlocksGroup(array $blocks, $group_id = '') {
    /** @var \Drupal\block\Entity\Block $block */
    foreach ($blocks as $block) {
      $config = $block->getVisibilityCondition('condition_group')
        ->getConfiguration();
      $config['block_visibility_group'] = $group_id;
      $block->setVisibilityConfig('condition_group', $config);
      $block->save();
    }
  }

  /**
   * Get all blocks in the Visibility Group.
   *
   * @return array
   *   The blocks for the group.
   */
  protected function getBlocksForGroup() {
    /** @var \Drupal\block\Entity\Block[] $all_blocks */
    $all_blocks = $this->blockStorage()->loadMultiple();
    $group_blocks = [];
    foreach ($all_blocks as $block) {
      if ($this->getGroupForBlock($block) == $this->entity->id()) {
        $group_blocks[$block->id()] = $block;
      }
    }
    return $group_blocks;
  }

  /**
   * Get Block Entity Storage.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   *   Gets the block storage.
   */
  protected function blockStorage() {
    return $this->entityManager->getStorage('block');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    if ($this->getBlocksForGroup()) {
      // If there are blocks in this group then
      // create a dropdown to let the user choose what to do with blocks.
      $options[static::UNSET_BLOCKS] = $this->t('Unset visibility group');
      $labels = $this->getBlockVisibilityLabels($this->entityManager->getStorage('block_visibility_group'));
      unset($labels[$this->entity->id()]);
      foreach ($labels as $type => $label) {
        $options[$type] = $this->t('Move blocks to group: <em>@label</em>', ['@label' => $label]);
      }
      $options[static::DELETE_BLOCKS] = $this->t('Delete all blocks');

      $form['blocks_op'] = [
        '#type' => 'select',
        '#title' => $this->t('Current blocks'),
        '#options' => $options,
        '#description' => $this->t('What do you want to do with the current blocks in this group?'),
      ];
    }
    else {
      // No blocks in this group.
      $form['no_blocks'] = [
        '#markup' => '<p>' . $this->t('There no blocks assigned to this group.') . '</p>',
      ];
    }

    return $form;
  }

}
