<?php

/**
 * @file
 * Contains \Drupal\pathauto\PathautoItem.
 */

namespace Drupal\pathauto;

use Drupal\path\Plugin\Field\FieldType\PathItem;

/**
 * Extends the default PathItem implementation to generate aliases.
 */
class PathautoItem extends PathItem {

  /**
   * {@inheritdoc}
   */
  public function insert() {
    // Only allow the parent implementation to act if pathauto will not create
    // an alias.
    if (!isset($this->pathauto) || empty($this->pathauto)) {
      parent::insert();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function update() {
    // Only allow the parent implementation to act if pathauto will not create
    // an alias.
    if (!isset($this->pathauto) || empty($this->pathauto)) {
      parent::update();
    }
  }

} 
