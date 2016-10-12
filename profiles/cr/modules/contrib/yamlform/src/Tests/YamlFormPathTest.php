<?php

namespace Drupal\yamlform\Tests;

/**
 * Tests for form path and page.
 *
 * @group YamlForm
 */
class YamlFormPathTest extends YamlFormTestBase {

  public static $modules = ['system', 'block', 'node', 'user', 'path', 'yamlform'];

  /**
   * Tests YAML page and title.
   */
  public function testPaths() {
    $yamlform = $this->createYamlForm();

    // Check default system submit path.
    $this->drupalGet('yamlform/' . $yamlform->id());
    $this->assertResponse(200, 'Submit system path exists');

    // Check default alias submit path.
    $this->drupalGet('form/' . str_replace('_', '-', $yamlform->id()));
    $this->assertResponse(200, 'Submit URL alias exists');

    // Check default alias confirm path.
    $this->drupalGet('form/' . str_replace('_', '-', $yamlform->id()) . '/confirmation');
    $this->assertResponse(200, 'Confirm URL alias exists');

    // Check page hidden (ie access denied).
    $yamlform->setSettings(['page' => FALSE])->save();
    $this->drupalGet('yamlform/' . $yamlform->id());
    $this->assertResponse(403, 'Submit system path access denied');
    $this->drupalGet('form/' . str_replace('_', '-', $yamlform->id()));
    $this->assertResponse(403, 'Submit URL alias access denied');

    // Check hidden page visible to admin.
    $this->drupalLogin($this->adminFormUser);
    $this->drupalGet('yamlform/' . $yamlform->id());
    $this->assertResponse(200, 'Submit system path access permitted');
    $this->drupalGet('form/' . str_replace('_', '-', $yamlform->id()));
    $this->assertResponse(200, 'Submit URL alias access permitted');
    $this->drupalLogout();

    // Check custom submit and confirm path.
    $yamlform->setSettings(['page_submit_path' => 'page_submit_path', 'page_confirm_path' => 'page_confirm_path'])->save();
    $this->drupalGet('page_submit_path');
    $this->assertResponse(200, 'Submit system path access permitted');
    $this->drupalGet('page_confirm_path');
    $this->assertResponse(200, 'Submit URL alias access permitted');

    // Check custom base path.
    $yamlform->setSettings([])->save();
    $this->drupalLogin($this->adminFormUser);
    $this->drupalPostForm('admin/structure/yamlform/settings', ['page[default_page_base_path]' => 'base/path'], t('Save configuration'));
    $this->drupalGet('base/path/' . str_replace('_', '-', $yamlform->id()));
    $this->assertResponse(200, 'Submit URL alias with custom base path exists');
    $this->drupalGet('base/path/' . str_replace('_', '-', $yamlform->id()) . '/confirmation');
    $this->assertResponse(200, 'Confirm URL alias with custom base path exists');
  }

}
