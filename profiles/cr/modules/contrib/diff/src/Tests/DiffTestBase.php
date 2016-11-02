<?php

namespace Drupal\diff\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Base class for Diff web tests.
 */
abstract class DiffTestBase extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'node',
    'diff',
    'block',
  ];

  /**
   * Permissions for the admin user.
   *
   * @var array
   */
  protected $adminPermissions = [
    'administer site configuration',
    'administer nodes',
    'administer content types',
    'create article content',
    'edit any article content',
    'view article revisions',
  ];

  /**
   * A user with content administrative permissions.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  protected function setUp() {
    parent::setUp();

    // Create the Article content type.
    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);

    // Place the blocks that Diff module uses.
    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('local_actions_block');
  }

  /**
   * Creates an user with admin permissions and log in.
   *
   * @param array $additional_permissions
   *   Additional permissions that will be granted to admin user.
   * @param bool $reset_permissions
   *   Flag to determine if default admin permissions will be replaced by
   *   $additional_permissions.
   *
   * @return object
   *   Newly created and logged in user object.
   */
  function loginAsAdmin($additional_permissions = [], $reset_permissions = FALSE) {
    $permissions = $this->adminPermissions;

    if ($reset_permissions) {
      $permissions = $additional_permissions;
    }
    elseif (!empty($additional_permissions)) {
      $permissions = array_merge($permissions, $additional_permissions);
    }

    $this->adminUser = $this->drupalCreateUser($permissions);
    $this->drupalLogin($this->adminUser);
    return $this->adminUser;
  }

}
