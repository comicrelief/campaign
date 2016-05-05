<?php

/**
 * @file
 * Definition of Drupal\yamlform\Tests\YamlFormDraftTest.
 */

namespace Drupal\yamlform\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\yamlform\Entity\YamlForm;

/**
 * Tests for YAML form draft.
 *
 * @group YamlForm
 */
class YamlFormDraftTest extends WebTestBase {

  use YamlFormTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'user', 'yamlform', 'yamlform_test'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    $this->purgeSubmissions();
    parent::tearDown();
  }

  /**
   * Test YAML form draft with autosave.
   */
  public function testDraftWithAutosave() {
    $account = $this->drupalCreateUser(['administer yamlform']);
    $this->drupalLogin($account);

    $yamlform = YamlForm::load('test_draft');

    // Save a draft.
    $sid = $this->postSubmission($yamlform, ['name' => 'John Smith'], t('Save a draft'));

    // Check saved draft message.
    $this->assertRaw('Your draft has been saved');
    $this->assertNoRaw('You have an existing draft');

    // Check loaded draft message.
    $this->drupalGet('yamlform/test_draft');
    $this->assertNoRaw('Your draft has been saved');
    $this->assertRaw('You have an existing draft');
    $this->assertFieldByName('name', 'John Smith');

    // Check submissions.
    $this->drupalGet('yamlform/test_draft/submissions');
    $this->assertRaw($sid . ' (draft)');

    // Check submission.
    $this->drupalGet('admin/structure/yamlform/results/manage/' . $sid);
    $this->assertRaw('<div><b>Is draft:</b> Yes</div>');

    // Check update draft and bypass validation.
    $this->drupalPostForm('yamlform/test_draft', [
      'name' => '',
      'comment' => 'Hello World!',
    ], t('Save a draft'));
    $this->assertRaw('Your draft has been saved');
    $this->assertNoRaw('You have an existing draft');
    $this->assertFieldByName('name', '');
    $this->assertFieldByName('comment', 'Hello World!');

    // Check preview of draft with valid data.
    $this->drupalPostForm('yamlform/test_draft', [
      'name' => 'John Smith',
      'comment' => 'Hello World!',
    ], t('Preview'));
    $this->assertNoRaw('Your draft has been saved');
    $this->assertNoRaw('You have an existing draft');
    $this->assertNoFieldByName('name', '');
    $this->assertNoFieldByName('comment', 'Hello World!');
    $this->assertRaw('<b>Name</b><br/>');
    $this->assertRaw('<b>Comment</b><br/>');
    $this->assertRaw('Please review your submission. Your submission is not complete until you press the &quot;Submit&quot; button!');

    // Save a valid draft.
    $this->drupalPostForm('yamlform/test_draft', [
      'name' => 'John Smith',
      'comment' => 'Hello World!',
    ], t('Save a draft'));

    // Check submit.
    $this->drupalPostForm('yamlform/test_draft', [], t('Submit'));
    $this->assertRaw('New submission added to Test: Draft.');

    // Check submission not in draft.
    $this->drupalGet('yamlform/test_draft');
    $this->assertNoRaw('Your draft has been saved');
    $this->assertNoRaw('You have an existing draft');
    $this->assertFieldByName('name', '');
    $this->assertFieldByName('comment', '');

    // Check submissions.
    $this->drupalGet('yamlform/test_draft/submissions');
    $this->assertNoRaw($sid . ' (draft)');

    // Check export with draft settings.
    $this->drupalGet('admin/structure/yamlform/manage/test_draft/results/download');
    $this->assertFieldByName('export[download][state]', 'all');

    // Check export without draft settings.
    $this->drupalGet('admin/structure/yamlform/manage/test_preview/results/download');
    $this->assertNoFieldByName('export[download][state]', 'all');

    // Check autosave on submit with validation errors.
    $this->drupalPostForm('yamlform/test_draft', [], t('Submit'));
    $this->assertRaw('Name field is required.');
    $this->drupalGet('yamlform/test_draft');
    $this->assertRaw('You have an existing draft');

    // Check autosave on preview.
    $this->drupalPostForm('yamlform/test_draft', ['name' => 'John Smith'], t('Preview'));
    $this->assertRaw('Please review your submission.');
    $this->drupalGet('yamlform/test_draft');
    $this->assertRaw('You have an existing draft');
    $this->assertFieldByName('name', 'John Smith');
  }

}
