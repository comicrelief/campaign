<?php

/**
 * @file
 * Contains \Drupal\color\Tests\ColorConfigSchemaTest.
 */

namespace Drupal\color\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Ensures the color config schema is correct.
 *
 * @group color
 */
class ColorConfigSchemaTest extends WebTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = array('color');

  /**
   * A user with administrative permissions.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    \Drupal::service('theme_handler')->install(array('bartik'));

    // Create user.
    $this->adminUser = $this->drupalCreateUser(array('administer themes'));
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Tests whether the color config schema is valid.
   */
  function testValidColorConfigSchema() {
    $settings_path = 'admin/appearance/settings/bartik';
    $edit['scheme'] = '';
    $edit['palette[bg]'] = '#123456';
    $this->drupalPostForm($settings_path, $edit, t('Save configuration'));
  }

}
