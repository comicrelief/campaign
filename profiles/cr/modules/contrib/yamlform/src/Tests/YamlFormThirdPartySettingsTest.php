<?php

namespace Drupal\yamlform\Tests;

/**
 * Tests for form third party settings.
 *
 * @group YamlForm
 */
class YamlFormThirdPartySettingsTest extends YamlFormTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'node', 'user', 'yamlform'];

  /**
   * Tests form third party settings.
   */
  public function testThirdPartySettings() {
    $this->drupalLogin($this->adminFormUser);

    // Check 'Form: Settings: Third party' shows no modules installed.
    $this->drupalGet('admin/structure/yamlform/settings/third-party');
    $this->assertRaw('There are no third party settings available.');

    // Check 'Contact: Settings: Third party' shows no modules installed.
    $this->drupalGet('admin/structure/yamlform/manage/contact/third-party-settings');
    $this->assertRaw('There are no third party settings available.');

    // Install test third party settings module.
    \Drupal::service('module_installer')->install(['yamlform_test_third_party_settings']);

    // Check 'Form: Settings: Third party' shows no modules installed.
    $this->drupalGet('admin/structure/yamlform/settings/third-party');
    $this->assertNoRaw('There are no third party settings available.');

    // Check 'Contact: Settings: Third party' shows no modules installed.
    $this->drupalGet('admin/structure/yamlform/manage/contact/third-party-settings');
    $this->assertNoRaw('There are no third party settings available.');

    // Check 'Form: Settings: Third party' message.
    $edit = [
      'third_party_settings[yamlform_test_third_party_settings][message]' => 'Message for all forms',
    ];
    $this->drupalPostForm('admin/structure/yamlform/settings/third-party', $edit, t('Save configuration'));
    $this->drupalGet('yamlform/contact');
    $this->assertRaw('Message for all forms');

    // Check that yamlform.settings.yml contain message.
    $this->assertEqual(
      'Message for all forms',
      $this->config('yamlform.settings')->get('third_party_settings.yamlform_test_third_party_settings.message')
    );

    // Check 'Contact: Settings: Third party' message.
    $edit = [
      'third_party_settings[yamlform_test_third_party_settings][message]' => 'Message for only this form',
    ];
    $this->drupalPostForm('admin/structure/yamlform/manage/contact/third-party-settings', $edit, t('Save'));
    $this->drupalGet('yamlform/contact');
    $this->assertRaw('Message for only this form');

    // Uninstall test third party settings module.
    \Drupal::service('module_installer')->uninstall(['yamlform_test_third_party_settings']);
    $this->drupalGet('yamlform/contact');
    $this->assertNoRaw('Message for only this form');

    // Check that yamlform.settings.yml no longer contains message or
    // yamlform_test_third_party_settings.
    $this->assertNull(
      $this->config('yamlform.settings')->get('third_party_settings.yamlform_test_third_party_settings.message')
    );
    $this->assertNull(
      $this->config('yamlform.settings')->get('third_party_settings.yamlform_test_third_party_settings')
    );
  }

}
