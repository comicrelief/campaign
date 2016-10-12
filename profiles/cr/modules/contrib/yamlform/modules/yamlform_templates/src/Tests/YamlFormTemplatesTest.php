<?php

namespace Drupal\yamlform_templates\Tests;

use Drupal\yamlform\Entity\YamlForm;
use Drupal\yamlform\Tests\YamlFormTestBase;

/**
 * Tests for form submission form settings.
 *
 * @group YamlFormTemplates
 */
class YamlFormTemplatesTest extends YamlFormTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'block', 'node', 'user', 'yamlform', 'yamlform_test', 'yamlform_templates'];

  /**
   * Tests form template setting.
   */
  public function testSettings() {
    $template_yamlform = YamlForm::load('test_form_template');

    // Check the templates always will remain closed.
    $this->assertTrue($template_yamlform->isClosed());
    $template_yamlform->setStatus(TRUE)->save();
    $this->assertTrue($template_yamlform->isClosed());

    // Login the own user.
    $this->drupalLogin($this->ownFormUser);

    // Check template is included in the 'Templates' list display.
    $this->drupalGet('admin/structure/yamlform/templates');
    $this->assertRaw('Test: Form: Template');
    $this->assertRaw('Test using a form as a template.');

    // Check template is accessible to user with create form access.
    $this->drupalGet('yamlform/test_form_template');
    $this->assertResponse(200);
    $this->assertRaw('You are previewing the below template,');

    // Login the admin user.
    $this->drupalLogin($this->adminFormUser);
  }

}
