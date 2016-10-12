<?php

namespace Drupal\yamlform\Tests;

use Drupal\yamlform\Entity\YamlForm;
use Drupal\yamlform\Entity\YamlFormSubmission;

/**
 * Tests for form handler plugin.
 *
 * @group YamlForm
 */
class YamlFormHandlerTest extends YamlFormTestBase {

  /**
   * Tests form handler plugin.
   */
  public function testYamlFormHandler() {
    $this->drupalLogin($this->adminFormUser);

    // Get the form test handler.
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform_handler_test */
    $yamlform_handler_test = YamlForm::load('test_handler_test');

    // Check new submission plugin invoking.
    $this->drupalGet('yamlform/test_handler_test');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:preCreate');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:postCreate');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:alterElements');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:alterForm');

    // Check validate submission plugin invoked and displaying an error.
    $this->postSubmission($yamlform_handler_test, ['element' => 'a value']);
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:preCreate');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:postCreate');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:alterElements');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:alterForm');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:validateForm');
    $this->assertRaw('The element must be empty. You entered <em class="placeholder">a value</em>.');
    $this->assertNoRaw('One two one two this is just a test');

    // Check submit submission plugin invoking.
    $sid = $this->postSubmission($yamlform_handler_test);
    $yamlform_submission = YamlFormSubmission::load($sid);
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:preCreate');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:postCreate');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:alterElements');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:alterForm');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:validateForm');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:submitForm');
    $this->assertRaw('One two one two this is just a test');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:confirmForm');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:preSave');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:postSave insert');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:postLoad');

    // Check update submission plugin invoking.
    $this->drupalPostForm('/admin/structure/yamlform/manage/test_handler_test/submission/' . $sid . '/edit', [], t('Submit'));
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:postSave update');

    // Check delete submission plugin invoking.
    $this->drupalPostForm('/admin/structure/yamlform/manage/test_handler_test/submission/' . $sid . '/delete', [], t('Delete'));
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:postLoad');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:preDelete');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:postDelete');
    $this->assertRaw('Submission #' . $yamlform_submission->serial() . ' has been deleted.');

    // Check configuration settings.
    $this->drupalPostForm('admin/structure/yamlform/manage/test_handler_test/handlers/test/edit', ['settings[message]' => '{message}'], t('Save'));
    $this->postSubmission($yamlform_handler_test);
    $this->assertRaw('{message}');

    // Check disabling a handler.
    $this->drupalPostForm('admin/structure/yamlform/manage/test_handler_test/handlers/test/edit', ['status' => FALSE], t('Save'));
    $this->drupalGet('yamlform/test_handler_test');
    $this->assertNoRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:preCreate');
    $this->assertNoRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:postCreate');
    $this->assertNoRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:alterElements');
    $this->assertNoRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:alterForm');

    // Enable the handler and disable the saving of results.
    $this->drupalPostForm('admin/structure/yamlform/manage/test_handler_test/handlers/test/edit', ['status' => TRUE], t('Save'));
    $yamlform_handler_test->setSettings(['results_disabled' => TRUE]);
    $yamlform_handler_test->save();

    // Check form disabled with saving of results is disabled and handler does
    // not process results.
    $this->drupalLogout();
    $this->drupalGet('yamlform/test_handler_test');
    $this->assertNoFieldByName('op', 'Submit');
    $this->assertNoRaw('This form is not saving or handling any submissions. All submitted data will be lost.');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:preCreate');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:postCreate');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:alterElements');
    $this->assertNoRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:alterForm');

    // Check admin can still post submission.
    $this->drupalLogin($this->adminFormUser);
    $this->drupalGet('yamlform/test_handler_test');
    $this->assertFieldByName('op', 'Submit');
    $this->assertRaw('This form is currently not saving any submitted data.');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:preCreate');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:postCreate');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:alterElements');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:alterForm');

    // Check submit submission plugin invoking when saving results is disabled.
    $yamlform_handler_test->setSetting('results_disabled', TRUE);
    $yamlform_handler_test->save();
    $this->postSubmission($yamlform_handler_test);
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:preCreate');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:postCreate');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:alterElements');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:alterForm');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:validateForm');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:submitForm');
    $this->assertRaw('One two one two this is just a test');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:confirmForm');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:preSave');
    $this->assertRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:postSave insert');
    // Check that post load is not executed when saving results is disabled.
    $this->assertNoRaw('Invoked: Drupal\yamlform_test\Plugin\YamlFormHandler\TestYamlFormHandler:postLoad');
  }

}
