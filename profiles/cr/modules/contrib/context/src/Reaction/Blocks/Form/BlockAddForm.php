<?php

namespace Drupal\context\Reaction\Blocks\Form;

use Drupal\Core\StringTranslation\TranslatableMarkup;

class BlockAddForm extends BlockFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'context_reaction_blocks_add_block_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getSubmitValue() {
    return $this->t('Add block');
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareBlock($block_id) {
    return $this->blockManager->createInstance($block_id);
  }

}
