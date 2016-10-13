<?php

namespace Drupal\yamlform\Tests;

use Drupal\yamlform\Entity\YamlForm;
use Drupal\yamlform\Entity\YamlFormSubmission;

/**
 * Tests for the form element plugin.
 *
 * @group YamlForm
 */
class YamlFormElementPluginTest extends YamlFormTestBase {

  /**
   * Tests form element plugin.
   */
  public function testYamlFormElement() {
    $this->drupalLogin($this->adminFormUser);

    // Get the form test element.
    $yamlform_plugin_test = YamlForm::load('test_element_plugin_test');

    // Check prepare and setDefaultValue().
    $this->drupalGet('yamlform/test_element_plugin_test');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:preCreate');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:postCreate');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:prepare');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:setDefaultValue');

    // Check save.
    $sid = $this->postSubmission($yamlform_plugin_test);
    $yamlform_submission = YamlFormSubmission::load($sid);
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:preCreate');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:prepare');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:setDefaultValue');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest::validate');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:preSave');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:postSave insert');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:postLoad');

    // Check update.
    $this->drupalPostForm('/admin/structure/yamlform/manage/test_element_plugin_test/submission/' . $sid . '/edit', [], t('Submit'));
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:postLoad');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:prepare');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:setDefaultValue');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest::validate');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:preSave');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:postSave update');

    // Check HTML.
    $this->drupalGet('/admin/structure/yamlform/manage/test_element_plugin_test/submission/' . $sid);
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:postLoad');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:formatHtml');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:formatText');

    // Check plain text.
    $this->drupalGet('/admin/structure/yamlform/manage/test_element_plugin_test/submission/' . $sid . '/text');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:postLoad');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:formatText');

    // Check delete.
    $this->drupalPostForm('/admin/structure/yamlform/manage/test_element_plugin_test/submission/' . $sid . '/delete', [], t('Delete'));
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:preDelete');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:postDelete');
    $this->assertRaw('Test: Element: Test (plugin): Submission #' . $yamlform_submission->serial() . ' has been deleted.');
  }

}
