<?php

/**
 * @file
 * Definition of Drupal\yamlform\test\YamlSubmissionFormSettingsTest.
 */

namespace Drupal\yamlform\Tests;

use Drupal\yamlform\Entity\YamlForm;

/**
 * Tests for YAML form submission form settings.
 *
 * @group YamlForm
 */
class YamlFormSubmissionFormSettingsTest extends YamlFormTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'block', 'filter', 'node', 'user', 'yamlform', 'yamlform_test'];

  /**
   * Tests YAML form setting including confirmation.
   */
  public function testSettings() {
    $this->drupalLogin($this->adminFormUser);

    /* Test confirmation message (confirmation_type=message) */

    // Check confirmation message.
    $this->drupalPostForm('yamlform/test_confirmation_message', [], t('Submit'));
    $this->assertRaw('This is a custom confirmation message.');
    $this->assertUrl('yamlform/test_confirmation_message');

    // Check confirmation page with custom query parameters.
    $this->drupalPostForm('yamlform/test_confirmation_message', [], t('Submit'), ['query' => ['custom' => 'param']]);
    $this->assertUrl('yamlform/test_confirmation_message', ['query' => ['custom' => 'param']]);

    /* Test confirmation inline (confirmation_type=inline) */

    $yamlform_confirmation_inline = YamlForm::load('test_confirmation_inline');

    // Check confirmation inline.
    $this->drupalPostForm('yamlform/test_confirmation_inline', [], t('Submit'));
    $this->assertRaw('This is a custom inline confirmation message.');
    $this->assertRaw('<a href="' . $yamlform_confirmation_inline->toUrl()->toString() . '" data-drupal-selector="edit-back-to" id="edit-back-to">Back to form</a>');
    $this->assertUrl('yamlform/test_confirmation_inline', ['query' => ['yamlform_id' => $yamlform_confirmation_inline->id()]]);

    // Check confirmation inline with custom query parameters.
    $this->drupalPostForm('yamlform/test_confirmation_inline', [], t('Submit'), ['query' => ['custom' => 'param']]);
    $this->assertRaw('<a href="' . $yamlform_confirmation_inline->toUrl()->toString() . '?custom=param" data-drupal-selector="edit-back-to" id="edit-back-to">Back to form</a>');
    $this->assertUrl('yamlform/test_confirmation_inline', ['query' => ['custom' => 'param', 'yamlform_id' => $yamlform_confirmation_inline->id()]]);

    /* Test confirmation page (confirmation_type=page) */

    $yamlform_confirmation_page = YamlForm::load('test_confirmation_page');

    // Check confirmation page.
    $this->drupalPostForm('yamlform/test_confirmation_page', [], t('Submit'));
    $this->assertRaw('This is a custom confirmation page.');
    $this->assertRaw('<a href="' . $yamlform_confirmation_page->toUrl()->toString() . '">Back to form</a>');
    $this->assertUrl('yamlform/test_confirmation_page/confirmation');

    // Check that the confirmation page's 'Back to form 'link includes custom
    // query parameters.
    $this->drupalGet('yamlform/test_confirmation_page/confirmation', ['query' => ['custom' => 'param']]);

    // Check confirmation page with custom query parameters.
    $this->drupalPostForm('yamlform/test_confirmation_page', [], t('Submit'), ['query' => ['custom' => 'param']]);
    $this->assertUrl('yamlform/test_confirmation_page/confirmation', ['query' => ['custom' => 'param']]);

    // TODO: (TESTING)  Figure out why the inline confirmation link is not including the query string parameters.
    // $this->assertRaw('<a href="' . $yamlform_confirmation_page->toUrl()->toString() . '?custom=param">Back to form</a>');

    /* Test confirmation URL (confirmation_type=url) */

    // Check confirmation URL.
    $this->drupalPostForm('yamlform/test_confirmation_url', [], t('Submit'));
    $this->assertRaw('This is a custom confirmation message.');
    $this->assertUrl('<front>');

    /* Test form closed (status=false) */

    $yamlform_form_closed = YamlForm::load('test_form_closed');
    $this->drupalLogout();

    // Check form closed message is displayed.
    $this->assertTrue($yamlform_form_closed->isClosed());
    $this->assertFalse($yamlform_form_closed->isOpen());
    $this->drupalGet('yamlform/test_form_closed');
    $this->assertNoRaw('This message should not be displayed)');
    $this->assertRaw('This form is closed.');

    // Check form closed message is displayed.
    $yamlform_form_closed->setSettings(['form_closed_message' => '']);
    $yamlform_form_closed->save();
    $this->drupalGet('yamlform/test_form_closed');
    $this->assertNoRaw('This form is closed.');
    $this->assertRaw('Sorry...This form is closed to new submissions.');

    // Check form is not closed for admins and warning is displayed.
    $this->drupalLogin($this->adminFormUser);
    $this->drupalGet('yamlform/test_form_closed');
    $this->assertRaw('This message should not be displayed');
    $this->assertNoRaw('This form is closed.');
    $this->assertRaw('Only submission administrators are allowed to access this form and create new submissions.');

    // Check form closed message is not displayed.
    $yamlform_form_closed->set('status', 1);
    $yamlform_form_closed->save();
    $this->assertFalse($yamlform_form_closed->isClosed());
    $this->assertTrue($yamlform_form_closed->isOpen());
    $this->drupalGet('yamlform/test_form_closed');
    $this->assertRaw('This message should not be displayed');
    $this->assertNoRaw('This form is closed.');
    $this->assertNoRaw('Only submission administrators are allowed to access this form and create new submissions.');

    /* Test form prepopulate (form_prepopulate) */

    $yamlform_form_prepopulate = YamlForm::load('test_form_prepopulate');

    // Check prepopulation of an input.
    $this->drupalGet('yamlform/test_form_prepopulate', ['query' => ['name' => 'John']]);
    $this->assertFieldByName('name', 'John');

    // Check disabling prepopulation of an input.
    $yamlform_form_prepopulate->setSettings(['form_prepopulate' => FALSE]);
    $yamlform_form_prepopulate->save();
    $this->drupalGet('yamlform/test_form_prepopulate', ['query' => ['name' => 'John']]);
    $this->assertFieldByName('name', '');

    /* Test form preview (form_preview) */

    $yamlform_preview = YamlForm::load('test_preview');

    // Check form with optional preview.
    $this->drupalGet('yamlform/test_preview');
    $this->assertFieldByName('op', 'Submit');
    $this->assertFieldByName('op', 'Preview');

    // Check default preview.
    $this->drupalPostForm('yamlform/test_preview', ['name' => 'test'], t('Preview'));

    $this->assertRaw('Please review your submission. Your submission is not complete until you press the &quot;Submit&quot; button!');
    $this->assertFieldByName('op', 'Submit');
    $this->assertFieldByName('op', '< Previous');
    $this->assertRaw('<b>Name</b><br/>test');

    // Check required preview with custom settings.
    $yamlform_preview->setSettings([
      'preview' => DRUPAL_REQUIRED,
      'preview_next_button_label' => '{Preview}',
      'preview_prev_button_label' => '{Back}',
      'preview_message' => '{Message}',
    ]);
    $yamlform_preview->save();

    // Check custom preview.
    $this->drupalPostForm('yamlform/test_preview', ['name' => 'test'], t('{Preview}'));
    $this->assertRaw('{Message}');
    $this->assertFieldByName('op', 'Submit');
    $this->assertFieldByName('op', '{Back}');
    $this->assertRaw('<b>Name</b><br/>test');

    $this->drupalGet('yamlform/test_preview');
    $this->assertNoFieldByName('op', 'Submit');
    $this->assertFieldByName('op', '{Preview}');

    /* Test results disabled (results_disabled=true) */

    // Check results disabled.
    $yamlform_results_disabled = YamlForm::load('test_results_disabled');
    $submission = $this->postSubmission($yamlform_results_disabled);
    $this->assertFalse($submission, 'Submission not saved to the database.');

    // Check error message form admins.
    $this->drupalGet('yamlform/test_results_disabled');
    $this->assertRaw(t('This form is currently not saving any submitted data.'));
    $this->assertFieldByName('op', 'Submit');
    $this->assertNoRaw(t('Unable to display this form. Please contact the site administrator.'));

    // Check form disable for everyone else.
    $this->drupalLogout();
    $this->drupalGet('yamlform/test_results_disabled');
    $this->assertNoRaw(t('This form is currently not saving any submitted data.'));
    $this->assertNoFieldByName('op', 'Submit');
    $this->assertRaw(t('Unable to display this form. Please contact the site administrator.'));

    /* Test limits */
    $yamlform_limit = YamlForm::load('test_limit');

    // Check form available.
    $this->drupalGet('yamlform/test_limit');
    $this->assertFieldByName('op', 'Submit');

    // Check user limit for authenticated user.
    $this->drupalLogin($this->normalUser);
    $this->postSubmission($yamlform_limit);

    // Check limit reached and form not available for authenticated user.
    $this->drupalGet('yamlform/test_limit');
    $this->assertNoFieldByName('op', 'Submit');
    $this->assertRaw('You are only allowed to have 1 submission for this form.');

    $this->drupalLogout();

    // Check form is still available for anonymous users.
    $this->drupalGet('yamlform/test_limit');
    $this->assertFieldByName('op', 'Submit');
    $this->assertNoRaw('You are only allowed to have 1 submission for this form.');

    // Add 3 more submissions making the total number of submissions equal to 3.
    $this->postSubmission($yamlform_limit);
    $this->postSubmission($yamlform_limit);

    // Check total limit.
    $this->assertNoFieldByName('op', 'Submit');
    $this->assertRaw('Only 3 submissions are allowed.');
    $this->assertNoRaw('You are only allowed to have 1 submission for this form.');

    // Check admin can still post submissions.
    $this->drupalLogin($this->adminFormUser);
    $this->drupalGet('yamlform/test_limit');
    $this->assertFieldByName('op', 'Submit');
    $this->assertRaw('Only 3 submissions are allowed.');
    $this->assertRaw('Only submission administrators are allowed to access this form and create new submissions.');
  }

}
