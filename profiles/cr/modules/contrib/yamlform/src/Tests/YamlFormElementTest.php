<?php

/**
 * @file
 * Definition of Drupal\yamlform\test\YamlFormSubmissionFormTest.
 */

namespace Drupal\yamlform\Tests;

use Drupal\yamlform\Entity\YamlForm;

/**
 * Tests for YAML form element plugin.
 *
 * @group YamlForm
 */
class YamlFormElementTest extends YamlFormTestBase {

  /**
   * Tests YAML form element plugin.
   */
  public function testYamlFormElement() {
    $this->drupalLogin($this->adminFormUser);

    // Get the YAML form test element.
    $yamlform_test_element = YamlForm::load('test_element_test');

    // Check prepare and setDefaultValue().
    $this->drupalGet('yamlform/test_element_test');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:prepare');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:setDefaultValue');

    // Check save.
    $sid = $this->postSubmission($yamlform_test_element);
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:prepare');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:setDefaultValue');
    $this->assertRaw('Invoked: \Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest::validate');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:save');

    // Check HTML.
    $this->drupalGet('/admin/structure/yamlform/results/manage/' . $sid);
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:formatHtml');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:formatText');

    // Check plain text.
    $this->drupalGet('/admin/structure/yamlform/results/manage/' . $sid . '/text');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:formatText');

    // Check delete.
    $this->drupalPostForm('/admin/structure/yamlform/results/manage/' . $sid . '/delete', [], t('Delete'));
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest:postDelete');
  }

}
