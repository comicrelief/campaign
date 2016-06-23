<?php
/**
 * @file
 * Contains \Drupal\simple_sitemap\Plugin\simple_sitemap\LinkGenerator\User.
 *
 * Plugin for user link generation.
 */

namespace Drupal\simple_sitemap\Plugin\simple_sitemap\LinkGenerator;

use Drupal\simple_sitemap\LinkGeneratorBase;

/**
 * User class.
 *
 * @LinkGenerator(
 *   id = "user",
 *   entity_type_name = "user",
 *   form_id = "user_admin_settings"
 * )
 */
class User extends LinkGeneratorBase {

  /**
   * {@inheritdoc}
   */
  public function getQueryInfo() {
    return array(
      'field_info' => array(
        'entity_id' => 'uid',
        'lastmod' => 'changed',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery($bundle) {
    return $this->database->select('users_field_data', 'u')
      ->fields('u', array('uid', 'changed'))
      ->condition('status', 1);
  }

}
