<?php

/**
 * @file
 * Contains \Drupal\config_readonly\Tests\ReadOnlyConfigTest.
 */

namespace Drupal\config_readonly\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\Core\Site\Settings;

/**
 * Tests read-only module config functionality.
 *
 * @group ConfigReadOnly
 */
class ReadOnlyConfigTest extends WebTestBase {

  public static $modules = ['config', 'config_readonly'];

  public function setUp() {
    parent::setUp();
    $this->adminUser = $this->createUser([], null, true);
    $this->drupalLogin($this->adminUser);
  }

  protected function turnOnReadOnlySetting() {
    $settings['settings']['config_readonly'] = (object) [
      'value' => TRUE,
      'required' => TRUE,
    ];
    $this->writeSettings($settings);
  }

  protected function turnOffReadOnlySetting() {
    $settings['settings']['config_readonly'] = (object) [
      'value' => FALSE,
      'required' => TRUE,
    ];
    $this->writeSettings($settings);
  }

  public function testModulePages() {
    $this->drupalGet('admin/modules');
    $this->assertNoText('This form will not be saved because the configuration active store is read-only.', 'Warning not shown on modules install page.');
    $this->drupalGet('admin/modules/uninstall');
    $this->assertNoText('This form will not be saved because the configuration active store is read-only.', 'Warning not shown on modules uninstall page.');
    $edit = [
      'modules[Core][action][enable]' => 'action',
    ];
    $this->drupalPostForm('admin/modules', $edit, t('Install'));
    $this->assertNoText('This form will not be saved because the configuration active store is read-only.', 'Able to install a module.');

    $this->turnOnReadOnlySetting();
    $this->drupalGet('admin/modules');
    $this->assertText('This form will not be saved because the configuration active store is read-only.', 'Warning shown on modules install page.');
    $this->drupalGet('admin/modules/uninstall');
    $this->assertText('This form will not be saved because the configuration active store is read-only.', 'Warning shown on modules uninstall page.');

    $this->drupalGet('admin/modules');
    $elements = $this->xpath("//form[@id='system-modules']//input[@id='edit-submit']");
    $install_button = isset($elements[0]) && $elements[0] instanceof \SimpleXMLElement ? $elements[0]->attributes() : FALSE;
    $this->assert($install_button !== FALSE, 'Found the install form submit button.');
    $this->assert((string) $install_button['disabled'] == 'disabled', 'The install modules form button is disabled.');
  }

  public function testSimpleConfig() {
    $this->drupalGet('admin/config/system/site-information');
    $this->assertNoText('This form will not be saved because the configuration active store is read-only.', 'Warning not shown on site information admin config page.');

    $this->turnOnReadOnlySetting();
    $this->drupalGet('admin/config/system/site-information');
    $this->assertText('This form will not be saved because the configuration active store is read-only.', 'Warning shown on site information admin config page.');
  }

  public function testSingleImport() {
    $this->drupalGet('admin/config/development/configuration/single/import');
    $this->assertNoText('This form will not be saved because the configuration active store is read-only.', 'Warning not shown on single config import page.');

    $this->turnOnReadOnlySetting();
    $this->drupalGet('admin/config/development/configuration/single/import');
    $this->assertText('This form will not be saved because the configuration active store is read-only.', 'Warning shown on single config import page.');
  }

}
