<?php
/**
 * @file
 * Contains \Drupal\paragraphs\Tests\ParagraphsAdministrationTest.
 */

namespace Drupal\paragraphs\Tests;

use Drupal\Core\Entity\Entity;
use Drupal\field_ui\Tests\FieldUiTestTrait;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\simpletest\WebTestBase;

/**
 * Tests the configuration of paragraphs.
 *
 * @group paragraphs
 */
class ParagraphsAdministrationTest extends WebTestBase {

  use FieldUiTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array(
    'node',
    'paragraphs',
    'field',
    'image',
    'field_ui',
    'block',
  );

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    // Create paragraphs and article content types.
    $this->drupalCreateContentType(array('type' => 'paragraphs', 'name' => 'Paragraphs'));
    $this->drupalCreateContentType(array('type' => 'article', 'name' => 'Article'));
    // Place the breadcrumb, tested in fieldUIAddNewField().
    $this->drupalPlaceBlock('system_breadcrumb_block');
    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('local_actions_block');
    $this->drupalPlaceBlock('page_title_block');
  }
  /**
   * Tests the revision of paragraphs.
   */
  public function testParagraphsRevisions() {
    $admin_user = $this->drupalCreateUser(array(
      'administer nodes',
      'create paragraphs content',
      'administer content types',
      'administer node fields',
      'administer node display',
      'administer paragraphs types',
      'administer paragraph fields',
      'administer node form display',
      'edit any paragraphs content',
    ));
    $this->drupalLogin($admin_user);

    $this->drupalGet('admin/structure/paragraphs_type');
    $this->clickLink(t('Add a Paragraphs type'));
    // Create paragraph type Headline + Block.
    $edit = array(
      'label' => 'Text',
      'id' => 'text',
    );
    $this->drupalPostForm(NULL, $edit, t('Save'));
    // Create field types for the text.
    static::fieldUIAddNewField('admin/structure/paragraphs_type/text', 'text', 'Text', 'text', array(), array());
    $this->assertText('Saved Text configuration.');

    // Create an article with paragraphs field.
    static::fieldUIAddNewField('admin/structure/types/manage/paragraphs', 'paragraphs', 'Paragraphs', 'entity_reference_revisions', array(
      'settings[target_type]' => 'paragraph',
      'cardinality' => '-1',
    ), array(
      'settings[handler_settings][target_bundles_drag_drop][text][enabled]' => TRUE,
    ));
    // Configure article fields.
    $this->drupalGet('admin/structure/types/manage/paragraphs/fields');
    $this->clickLink(t('Manage form display'));
    $this->drupalPostForm(NULL, array('fields[field_paragraphs][type]' => 'entity_reference_paragraphs'), t('Save'));

    // Create node with our paragraphs.
    $this->drupalGet('node/add/paragraphs');
    $this->drupalPostAjaxForm(NULL, array(), 'field_paragraphs_text_add_more');
    $this->drupalPostAjaxForm(NULL, array(), 'field_paragraphs_text_add_more');
    $edit = [
      'title[0][value]' => 'TEST TITEL',
      'field_paragraphs[0][subform][field_text][0][value]' => 'Test text 1',
      'field_paragraphs[1][subform][field_text][0][value]' => 'Test text 2',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and publish'));

    // Edit the just created node. Create new revision.
    $this->drupalGet('node/1/edit');
    $edit = [
      'title[0][value]' => 'TEST TITLE',
      'field_paragraphs[0][subform][field_text][0][value]' => 'Foo Bar 2',
      'revision' => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and keep published'));

    // Assert the paragraphs have been changed.
    $this->assertNoText('Test text 1');
    $this->assertText('Test text 2');
    $this->assertText('Foo Bar 2');
    $this->assertText('TEST TITLE');

    // Check out the revisions page and assert there are 2 revisions.
    $this->drupalGet('node/1/revisions');
    $rows = $this->xpath('//tbody/tr');
    // Make sure two revisions available.
    $this->assertEqual(count($rows), 2);
    // Revert to the old version.
    $this->clickLink(t('Revert'));
    $this->drupalPostForm(NULL, [], t('Revert'));
    $this->drupalGet('node/1');
    // Assert the node has been reverted.
    $this->assertNoText('Foo Bar 2');
    $this->assertText('Test text 2');
    $this->assertText('Test text 1');
    $this->assertText('TEST TITEL');
  }


  /**
   * Tests the paragraph creation.
   */
  public function testParagraphsCreation() {
    $admin_user = $this->drupalCreateUser(array(
      'administer site configuration',
      'administer nodes',
      'create article content',
      'create paragraphs content',
      'administer content types',
      'administer node fields',
      'administer node display',
      'administer paragraphs types',
      'administer paragraph fields',
      'administer paragraph display',
      'administer paragraph form display',
      'administer node form display',
      'edit any article content',
      'delete any article content'
    ));
    $this->drupalLogin($admin_user);

    $this->drupalGet('admin/structure/paragraphs_type');
    $this->clickLink(t('Add a Paragraphs type'));
    // Create paragraph type text + image.
    $edit = array(
      'label' => 'Text + Image',
      'id' => 'text_image'
    );
    $this->drupalPostForm(NULL, $edit, t('Save'));
    // Create field types for text and image.
    static::fieldUIAddNewField('admin/structure/paragraphs_type/text_image', 'text', 'Text', 'text_long', array(), array());
    $this->assertText('Saved Text configuration.');
    static::fieldUIAddNewField('admin/structure/paragraphs_type/text_image', 'image', 'Image', 'image', array(), array('settings[alt_field_required]' => FALSE));
    $this->assertText('Saved Image configuration.');

    // Create paragraph type image.
    $edit = array(
      'label' => 'Image only',
      'id' => 'image'
    );
    $this->drupalPostForm('admin/structure/paragraphs_type/add', $edit, t('Save'));
    // Create field types for image.
    static::fieldUIAddNewField('admin/structure/paragraphs_type/image', 'image_only', 'Image only', 'image', array(), array());
    $this->assertText('Saved Image only configuration.');

    $this->drupalGet('admin/structure/paragraphs_type');
    $rows = $this->xpath('//tbody/tr');
    // Make sure 2 types are available with their label.
    $this->assertEqual(count($rows), 2);
    $this->assertText('Text + Image');
    $this->assertText('Image only');
    // Make sure there is an edit link for each type.
    $this->clickLink(t('Edit'));
    // Make sure the field UI appears.
    $this->assertLink('Manage fields');
    $this->assertLink('Manage form display');
    $this->assertLink('Manage display');
    $this->assertTitle('Edit Image only paragraph type | Drupal');

    // Create an article with paragraphs field.
    static::fieldUIAddNewField('admin/structure/types/manage/article', 'paragraphs', 'Paragraphs', 'entity_reference_revisions', array(
      'settings[target_type]' => 'paragraph',
      'cardinality' => '-1'
    ), array(
      'settings[handler_settings][target_bundles_drag_drop][image][enabled]' => TRUE,
      'settings[handler_settings][target_bundles_drag_drop][text_image][enabled]' => TRUE,
      'description' => 'Help text.',
    ));
    // Configure article fields.
    $this->drupalGet('admin/structure/types/manage/article/fields');
    $this->clickLink(t('Edit'), 1);
    $this->drupalPostForm(NULL, NULL, t('Save settings'));
    $this->clickLink(t('Manage display'));
    $this->drupalPostForm(NULL, array('fields[field_paragraphs][type]' => 'entity_reference_revisions_entity_view'), t('Save'));
    $this->clickLink(t('Manage form display'));
    $this->drupalPostForm(NULL, array('fields[field_paragraphs][type]' => 'entity_reference_paragraphs'), t('Save'));

    // Test for "Add mode" setting.
    $this->drupalGet('admin/structure/types/manage/article/form-display');
    $field_name = 'field_paragraphs';

    // Click on the widget settings button to open the widget settings form.
    $this->drupalPostAjaxForm(NULL, array(), $field_name . "_settings_edit");

    // Enable setting.
    $edit = array('fields[' . $field_name . '][settings_edit_form][settings][add_mode]' => 'button');
    $this->drupalPostForm(NULL, $edit, t('Save'));

    // Check if the setting is stored.
    $this->drupalGet('admin/structure/types/manage/article/form-display');
    $this->assertText('Add mode: Buttons', 'Checking the settings value.');

    $this->drupalPostAjaxForm(NULL, array(), $field_name . "_settings_edit");
    // Assert the 'Buttons' option is selected.
    $this->assertOptionSelected('edit-fields-field-paragraphs-settings-edit-form-settings-add-mode', 'button', 'Updated value is correct!.');

    // Add two Text + Image paragraphs in article.
    $this->drupalGet('node/add/article');

    // Check if help text is saved correctly.
    $this->assertText('Help text.');

    // Checking changes on article.
    $this->assertRaw('<div class="paragraphs-dropbutton-wrapper"><input', 'Updated value in article.');

    $this->drupalPostAjaxForm(NULL, array(), 'field_paragraphs_text_image_add_more');
    $this->drupalPostAjaxForm(NULL, array(), 'field_paragraphs_text_image_add_more');
    // Create an 'image' file, upload it.
    $text = 'Trust me I\'m an image';
    file_put_contents('temporary://myImage1.jpg', $text);
    file_put_contents('temporary://myImage2.jpg', $text);

    $edit = array(
      'title[0][value]' => 'Test article',
      'field_paragraphs[0][subform][field_text][0][value]' => 'Test text 1',
      'files[field_paragraphs_0_subform_field_image_0]' => drupal_realpath('temporary://myImage1.jpg'),
      'field_paragraphs[1][subform][field_text][0][value]' => 'Test text 2',
      'files[field_paragraphs_1_subform_field_image_0]' => drupal_realpath('temporary://myImage2.jpg'),
    );
    $this->drupalPostForm(NULL, $edit, t('Save and publish'));

    $node = $this->drupalGetNodeByTitle('Test article');
    $img1_url = file_create_url(\Drupal::token()->replace('public://[date:custom:Y]-[date:custom:m]/myImage1.jpg'));
    $img2_url = file_create_url(\Drupal::token()->replace('public://[date:custom:Y]-[date:custom:m]/myImage2.jpg'));

    // Check the text and image after publish.
    $this->assertText('Test text 1');
    $this->assertRaw('<img src="' . file_url_transform_relative($img1_url));
    $this->assertText('Test text 2');
    $this->assertRaw('<img src="' . file_url_transform_relative($img2_url));

    // Tests for "Edit mode" settings.
    // Test for closed setting.
    $this->drupalGet('admin/structure/types/manage/article/form-display');
    // Click on the widget settings button to open the widget settings form.
    $this->drupalPostAjaxForm(NULL, array(), "field_paragraphs_settings_edit");
    // Enable setting.
    $edit = array('fields[field_paragraphs][settings_edit_form][settings][edit_mode]' => 'closed');
    $this->drupalPostForm(NULL, $edit, t('Save'));
    // Check if the setting is stored.
    $this->assertText('Edit mode: Closed', 'Checking the settings value.');
    $this->drupalPostAjaxForm(NULL, array(), "field_paragraphs_settings_edit");
    // Assert the 'Closed' option is selected.
    $this->assertOptionSelected('edit-fields-field-paragraphs-settings-edit-form-settings-edit-mode', 'closed', 'Updated value correctly.');
    $this->drupalGet('node/1/edit');
    // The textareas for paragraphs should not be visible.
    $this->assertNoRaw('field_paragraphs[0][subform][field_text][0][value]');
    $this->assertNoRaw('field_paragraphs[1][subform][field_text][0][value]');
    $this->assertNoText('Test text 1');
    $this->assertNoText('Test text 2');

    // Test for preview option.
    $this->drupalGet('admin/structure/types/manage/article/form-display');
    $this->drupalPostAjaxForm(NULL, array(), "field_paragraphs_settings_edit");
    $edit = array('fields[field_paragraphs][settings_edit_form][settings][edit_mode]' => 'preview');
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertText('Edit mode: Preview', 'Checking the settings value.');
    $this->drupalGet('node/1/edit');
    // The texts in the paragraphs should be visible.
    $this->assertNoRaw('field_paragraphs[0][subform][field_text][0][value]');
    $this->assertNoRaw('field_paragraphs[1][subform][field_text][0][value]');
    $this->assertText('Test text 1');
    $this->assertText('Test text 2');

    // Test for open option.
    $this->drupalGet('admin/structure/types/manage/article/form-display');
    $this->drupalPostAjaxForm(NULL, array(), "field_paragraphs_settings_edit");
    // Assert the 'Preview' option is selected.
    $this->assertOptionSelected('edit-fields-field-paragraphs-settings-edit-form-settings-edit-mode', 'preview', 'Updated value correctly.');
    // Restore the value to Open for next test.
    $edit = array('fields[field_paragraphs][settings_edit_form][settings][edit_mode]' => 'open');
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->drupalGet('node/1/edit');
    // The textareas for paragraphs should be visible.
    $this->assertRaw('field_paragraphs[0][subform][field_text][0][value]');
    $this->assertRaw('field_paragraphs[1][subform][field_text][0][value]');

    $paragraphs = Paragraph::loadMultiple();
    $this->assertEqual(count($paragraphs), 2, 'Two paragraphs in article');

    // Check article edit page.
    $this->drupalGet('node/' . $node->id() . '/edit');
    // Check both paragraphs in edit page.
    $this->assertFieldByName('field_paragraphs[0][subform][field_text][0][value]', 'Test text 1');
    $this->assertRaw('<a href="' . $img1_url . '" type="image/jpeg; length=21">myImage1.jpg</a>');
    $this->assertFieldByName('field_paragraphs[1][subform][field_text][0][value]', 'Test text 2');
    $this->assertRaw('<a href="' . $img2_url . '" type="image/jpeg; length=21">myImage2.jpg</a>');
    // Remove 2nd paragraph.
    $this->drupalPostForm(NULL, NULL, t('Remove'));
    $this->assertNoField('field_paragraphs[1][subform][field_text][0][value]');
    $this->assertNoRaw('<a href="' . $img2_url . '" type="image/jpeg; length=21">myImage2.jpg</a>');
    // Restore it again.
    $this->drupalPostForm(NULL, NULL, t('Restore'));
    $this->assertFieldByName('field_paragraphs[1][subform][field_text][0][value]', 'Test text 2');
    $this->assertRaw('<a href="' . $img2_url . '" type="image/jpeg; length=21">myImage2.jpg</a>');
    // @todo enable below test in 2428833.
//    // Remove it and confirm.
//    $this->drupalPostAjaxForm(NULL, NULL, array('field_paragraphs_1_subform_field_image_0_remove_button' => t('Remove')));
//    $this->drupalPostAjaxForm(NULL, NULL, array('field_paragraphs_1_confirm_remove' => t('Confirm removal')));
//    $this->drupalPostForm(NULL, NULL, t('Save and keep published'));
//    $current_paragraphs  = Paragraph::loadMultiple();
//    debug(count($current_paragraphs));
//    $this->assertEqual(count($current_paragraphs), 1, 'Only one paragraph in article');

    // Delete the node.
    $this->clickLink(t('Delete'));
    $this->drupalPostForm(NULL, NULL, t('Delete'));
    $this->assertText('Test article has been deleted.');
    // @todo enable below tests in 2429335.
//    // Make sure two paragraph entities have been deleted.
//    $current_paragraphs = Paragraph::loadMultiple();
//    $this->assertTrue(empty($current_paragraphs));

    // Check if the publish/unpublish option works.
    $this->drupalGet('admin/structure/paragraphs_type/text_image/form-display');
    $edit = [
      'fields[status][type]' => 'boolean_checkbox',
    ];

    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->drupalGet('node/add/article');
    $this->drupalPostForm(NULL, NULL, t('Add Text + Image'));
    $this->assertRaw('edit-field-paragraphs-0-subform-status-value');
    $edit = [
      'title[0][value]' => 'Example publish/unpublish',
      'field_paragraphs[0][subform][field_text][0][value]' => 'Example published and unpublished',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and publish'));
    $this->assertText(t('Example published and unpublished'));
    $this->clickLink(t('Edit'));
    $edit = [
      'field_paragraphs[0][subform][status][value]' => FALSE,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and keep published'));
    $this->assertNoText(t('Example published and unpublished'));
  }

  /**
   * Asserts that a select option in the current page is checked.
   *
   * @param string $id
   *   ID of select field to assert.
   * @param string $option
   *   Option to assert.
   * @param string $message
   *   (optional) A message to display with the assertion. Do not translate
   *   messages: use format_string() to embed variables in the message text, not
   *   t(). If left blank, a default message will be displayed.
   * @param string $group
   *   (optional) The group this message is in, which is displayed in a column
   *   in test output. Use 'Debug' to indicate this is debugging output. Do not
   *   translate this string. Defaults to 'Browser'; most tests do not override
   *   this default.
   *
   * @return bool
   *   TRUE on pass, FALSE on fail.
   *
   * @todo Remove function once core issue is resolved: https://www.drupal.org/node/2530092
   */
  protected function assertOptionSelected($id, $option, $message = '', $group = 'Browser') {
    $elements = $this->xpath('//select[contains(@id, :id)]//option[@value=:option]', array(':id' => $id, ':option' => $option));
    return $this->assertTrue(isset($elements[0]) && !empty($elements[0]['selected']), $message ? $message : SafeMarkup::format('Option @option for field @id is selected.', array('@option' => $option, '@id' => $id)), $group);
  }

}
