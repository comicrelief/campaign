<?php

/**
 * @file
 * Contains \Drupal\diff\AdminFormsTest.php.
 *
 * @ingroup diff
 */

namespace Drupal\diff\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests the Diff admin forms.
 *
 * @group diff
 */
class AdminFormsTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['node', 'field_ui', 'diff'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create Article node type.
    $this->drupalCreateContentType(array('type' => 'article', 'name' => 'Article'));

    $this->drupalLogin($this->rootUser);
  }

  /**
   * Tests the Settings tab.
   */
  public function testSettingsTab() {
    $edit = [
      'theme' => 'github',
      'radio_behavior' => 'linear',
      'context_lines_leading' => 10,
      'context_lines_trailing' => 5,
    ];
    $this->drupalPostForm('admin/config/content/diff/general', $edit, t('Save configuration'));
    $this->assertText('The configuration options have been saved.');
  }

  /**
   * Tests the Configurable Fields tab.
   */
  public function testConfigurableFieldsTab() {
    $this->drupalGet('admin/config/content/diff/fields');
    $this->drupalPostAjaxForm(NULL, [], 'text_settings_edit');
    $this->assertText('Plugin settings: Text Field Diff');
    $edit = [
      'fields[text][settings_edit_form][settings][show_header]' => TRUE,
      'fields[text][settings_edit_form][settings][compare_format]' => TRUE,
      'fields[text][settings_edit_form][settings][markdown]' => 'filter_xss_all',
    ];
    $this->drupalPostForm(NULL, $edit, t('Update'));
    $this->assertText('You have unsaved changes.');
    $this->drupalPostForm(NULL, [], t('Save'));
    $this->assertText('Your settings have been saved.');
  }

  /**
   * Tests the Base fields tab.
   */
  public function testBaseFieldsTab() {
    $edit = [
      'nid' => TRUE,
      'status' => TRUE,
    ];
    $this->drupalPostForm('admin/config/content/diff/entities/node', $edit, t('Save configuration'));
    $this->assertText('The configuration options have been saved.');
  }

  /**
   * Tests the Compare Revisions vertical tab.
   */
  public function testCompareRevisionsTab() {
    $edit = [
      'view_mode' => 'full',
    ];
    $this->drupalPostForm('admin/structure/types/manage/article', $edit, t('Save content type'));
    $this->assertText('The content type Article has been updated.');
  }

}
