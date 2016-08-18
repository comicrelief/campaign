<?php

namespace Drupal\entity_reference_revisions;

/**
 * Trait for EntityNeedsSaveInterface.
 */
trait EntityNeedsSaveTrait {

  /**
   * Whether the entity needs to be saved or not.
   *
   * @var bool
   */
  protected $needsSave = FALSE;

  /**
   * {@inheritdoc}
   */
  public function needsSave() {
    return $this->needsSave;
  }

  /**
   * {@inheritdoc}
   */
  public function setNeedsSave($needs_save) {
    $this->needsSave = $needs_save;
  }

}
