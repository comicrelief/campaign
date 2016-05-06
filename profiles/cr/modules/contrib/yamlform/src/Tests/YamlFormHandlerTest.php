<?php

/**
 * @file
 * Definition of Drupal\yamlform\test\YamlFormHandlerTest.
 */

namespace Drupal\yamlform\Tests;

use Drupal\yamlform\Entity\YamlForm;

/**
 * Tests for YAML form handler plugin.
 *
 * @group YamlForm
 */
class YamlFormHandlerTest extends YamlFormTestBase {

  /**
   * Tests YAML form handler plugin.
   */
  public function testYamlFormHandler() {
    $this->drupalLogin($this->adminFormUser);

    // Get the YAML form test handler.
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform_test_handler */
    $yamlform_test_handler = YamlForm::load('test_handler_test');

    // Check new submission plugin invoking.
    $this->drupalGet('yamlform/test_handler_test');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:preCreate');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:postCreate');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:alterInputs');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:alterForm');

    // Check submit submission plugin invoking.
    $sid = $this->postSubmission($yamlform_test_handler);
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:preCreate');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:postCreate');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:alterInputs');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:alterForm');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:validateForm');
    $this->assertRaw('One two one two this is just a test');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:submitForm');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:preSave');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:postSave insert');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:postLoad');

    // Check update submission plugin invoking.
    $this->drupalPostForm('/admin/structure/yamlform/results/manage/' . $sid . '/edit', [], t('Submit'));
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:postSave update');

    // Check delete submission plugin invoking.
    $this->drupalPostForm('/admin/structure/yamlform/results/manage/' . $sid . '/delete', [], t('Delete'));
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:postLoad');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:preDelete');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:postDelete');
    $this->assertRaw('Submission #' . $sid . ' has been deleted.');

    // Check configuration settings.
    $this->drupalPostForm('admin/structure/yamlform/manage/test_handler_test/handlers/test', ['settings[message]' => '{message}'], t('Update'));
    $this->postSubmission($yamlform_test_handler);
    $this->assertRaw('{message}');

    // Check disabling a handler.
    $this->drupalPostForm('admin/structure/yamlform/manage/test_handler_test/handlers/test', ['status' => FALSE], t('Update'));
    $this->drupalGet('yamlform/test_handler_test');
    $this->assertNoRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:preCreate');
    $this->assertNoRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:postCreate');
    $this->assertNoRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:alterInputs');
    $this->assertNoRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:alterForm');

    // Enable the handler and disable the saving of results.
    $this->drupalPostForm('admin/structure/yamlform/manage/test_handler_test/handlers/test', ['status' => TRUE], t('Update'));
    $yamlform_test_handler->setSettings(['results_disabled' => TRUE]);
    $yamlform_test_handler->save();

    // Check form disabled with saving of results is disabled and handler does not process results.
    $this->drupalLogout();
    $this->drupalGet('yamlform/test_handler_test');
    $this->assertNoFieldByName('op', 'Submit');
    $this->assertNoRaw('This form is not saving or handling any submissions. All submitted data will be lost.');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:preCreate');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:postCreate');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:alterInputs');
    $this->assertNoRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:alterForm');

    // Check admin can still post submission.
    $this->drupalLogin($this->adminFormUser);
    $this->drupalGet('yamlform/test_handler_test');
    $this->assertFieldByName('op', 'Submit');
    $this->assertRaw('This form is currently not saving any submitted data.');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:preCreate');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:postCreate');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:alterInputs');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:alterForm');

  }

}
