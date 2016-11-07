<?php

namespace Drupal\ds\Plugin\DsField\User;

use Drupal\ds\Plugin\DsField\Title;

/**
 * Plugin that renders the username.
 *
 * @DsField(
 *   id = "username",
 *   title = @Translation("Username"),
 *   entity_type = "user",
 *   provider = "user"
 * )
 */
class Username extends Title {

  /**
   * {@inheritdoc}
   */
  public function entityRenderKey() {
    return 'name';
  }

}
