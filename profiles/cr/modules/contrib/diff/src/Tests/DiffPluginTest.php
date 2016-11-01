<?php

/**
 * @ingroup diff
 */

namespace Drupal\diff\Tests;

use Drupal\comment\Plugin\Field\FieldType\CommentItemInterface;
use Drupal\comment\Tests\CommentTestTrait;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field_ui\Tests\FieldUiTestTrait;
use Drupal\link\LinkItemInterface;

/**
 * Tests the Diff module plugins.
 *
 * @group diff
 */
class DiffPluginTest extends DiffTestBase {

  use CommentTestTrait;
  use FieldUiTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'diff_test',
    'comment',
    'file',
    'image',
    'link',
    'options',
    'field_ui',
  ];

  /**
   * A storage instance for the entity form display.
   *
   * @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface
   */
  protected $formDisplay;

  /**
   * A storage instance for the entity view display.
   *
   * @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface
   */
  protected $viewDisplay;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Add the comment field to articles.
    $this->addDefaultCommentField('node', 'article');

    $this->formDisplay = \Drupal::entityTypeManager()->getStorage('entity_form_display');
    $this->viewDisplay = \Drupal::entityTypeManager()->getStorage('entity_view_display');
    $this->fileSystem = \Drupal::service('file_system');

    // FieldUiTestTrait checks the breadcrumb when adding a field, so we need
    // to show the breadcrumb block.
    $this->drupalPlaceBlock('system_breadcrumb_block');
    $this->drupalLogin($this->rootUser);
  }

  /**
   * Tests the comment plugin.
   */
  public function testCommentPlugin() {
    // Create an article with comments enabled..
    $title = 'Sample article';
    $edit = array(
      'title[0][value]' => $title,
      'body[0][value]' => '<p>Revision 1</p>',
      'comment[0][status]' => CommentItemInterface::OPEN,
    );
    $this->drupalPostForm('node/add/article', $edit, t('Save and publish'));
    $node = $this->drupalGetNodeByTitle($title);

    // Edit the article and close its comments.
    $edit = array(
      'comment[0][status]' => CommentItemInterface::CLOSED,
      'revision' => TRUE,
    );
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save and keep published'));

    // Check the difference between the last two revisions.
    $this->clickLink(t('Revisions'));
    $this->drupalPostForm(NULL, NULL, t('Compare'));
    $this->assertText('Comments');
    $this->assertText('Comments for this entity are open.');
    $this->assertText('Comments for this entity are closed.');
  }

  /**
   * Tests the Core plugin.
   */
  public function testCorePlugin() {
    // Add an email field (supported by the Diff core plugin) to the Article
    // content type.
    $field_name = 'field_email';
    $this->fieldStorage = FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
      'type' => 'email',
    ]);
    $this->fieldStorage->save();
    $this->field = FieldConfig::create([
      'field_storage' => $this->fieldStorage,
      'bundle' => 'article',
      'label' => 'Email',
    ]);
    $this->field->save();

    // Add the email field to the article form.
    $this->formDisplay->load('node.article.default')
      ->setComponent($field_name, ['type' => 'email_default'])
      ->save();

    // Add the email field to the default display
    $this->viewDisplay->load('node.article.default')
      ->setComponent($field_name, ['type' => 'basic_string'])
      ->save();

    // Create an article with an email.
    $node = $this->drupalCreateNode([
      'type' => 'article',
      'field_email' => 'foo@example.com',
    ]);

    // Edit the article and change the email.
    $edit = array(
      'field_email[0][value]' => 'bar@example.com',
      'revision' => TRUE,
    );
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save and keep published'));

    // Check the difference between the last two revisions.
    $this->clickLink(t('Revisions'));
    $this->drupalPostForm(NULL, NULL, t('Compare'));
    $this->assertText('Email');
    $this->assertText('foo@example.com');
    $this->assertText('bar@example.com');
  }

  /**
   * Tests the Core plugin with a timestamp field.
   */
  public function testCorePluginTimestampField() {
    // Add a timestamp field (supported by the Diff core plugin) to the Article
    // content type.
    $field_name = 'field_timestamp';
    $this->fieldStorage = FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
      'type' => 'timestamp',
    ]);
    $this->fieldStorage->save();
    $this->field = FieldConfig::create([
      'field_storage' => $this->fieldStorage,
      'bundle' => 'article',
      'label' => 'Timestamp test',
    ]);
    $this->field->save();

    // Add the timestamp field to the article form.
    $this->formDisplay->load('node.article.default')
      ->setComponent($field_name, ['type' => 'datetime_timestamp'])
      ->save();

    // Add the timestamp field to the default display
    $this->viewDisplay->load('node.article.default')
      ->setComponent($field_name, ['type' => 'timestamp'])
      ->save();

    $old_timestamp = '321321321';
    $new_timestamp = '123123123';

    // Create an article with an timestamp.
    $this->drupalCreateNode([
      'title' => 'timestamp_test',
      'type' => 'article',
      'field_timestamp' => $old_timestamp,
    ]);

    // Create a new revision with an updated timestamp.
    $node = $this->drupalGetNodeByTitle('timestamp_test');
    $node->field_timestamp = $new_timestamp;
    $node->setNewRevision(TRUE);
    $node->save();

    // Compare the revisions.
    $this->drupalGet('node/' . $node->id() . '/revisions');
    $this->drupalPostForm(NULL, NULL, t('Compare'));

    // Assert that the timestamp field does not show a unix time format.
    $this->assertText('Timestamp test');
    $date_formatter = \Drupal::service('date.formatter');
    $this->assertText($date_formatter->format($old_timestamp));
    $this->assertText($date_formatter->format($new_timestamp));
  }

  /**
   * Tests the changed field without plugins.
   */
  public function testFieldWithNoPlugin() {
    // Create an article.
    $node = $this->drupalCreateNode([
      'type' => 'article',
    ]);

    // Update the article and add a new revision, the "changed" field should be
    // updated which does not have plugins provided by diff.
    $edit = array(
      'revision' => TRUE,
    );
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save and keep published'));

    // Check the difference between the last two revisions.
    $this->clickLink(t('Revisions'));
    $this->drupalPostForm(NULL, NULL, t('Compare'));

    // "changed" field is not displayed since there is no plugin for it. This
    // should not break the revisions comparison display.
    $this->assertResponse(200);
    $this->assertLink(t('Split fields'));
  }

  /**
   * Tests the EntityReference plugin.
   */
  public function testEntityReferencePlugin() {
    // Add an entity reference field to the article content type.
    $bundle_path = 'admin/structure/types/manage/article';
    $field_name = 'reference';
    $storage_edit = $field_edit = array();
    $storage_edit['settings[target_type]'] = 'node';
    $field_edit['settings[handler_settings][target_bundles][article]'] = TRUE;
    $this->fieldUIAddNewField($bundle_path, $field_name, 'Reference', 'entity_reference', $storage_edit, $field_edit);

    // Create three article nodes.
    $node = $this->drupalCreateNode([
      'type' => 'article',
      'title' => 'Article A',
    ]);
    $this->drupalCreateNode([
      'type' => 'article',
      'title' => 'Article B',
    ]);
    $this->drupalCreateNode([
      'type' => 'article',
      'title' => 'Article C',
    ]);

    // Reference article B in article A.
    $edit = array(
      'field_reference[0][target_id]' => 'Article B (2)',
      'revision' => TRUE,
    );
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save and keep published'));

    // Update article A so it points to article C instead of B.
    $edit = array(
      'field_reference[0][target_id]' => 'Article C (3)',
      'revision' => TRUE,
    );
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save and keep published'));

    // Check differences between revisions.
    $this->clickLink(t('Revisions'));
    $this->drupalPostForm(NULL, NULL, t('Compare'));
    $this->assertText('Reference');
    $this->assertText('Article B');
    $this->assertText('Article C');
  }

  /**
   * Tests the File plugin.
   */
  public function testFilePlugin() {
    // Add file field to the article content type.
    $file_field_name = 'field_file';
    $field_storage = FieldStorageConfig::create(array(
      'field_name' => $file_field_name,
      'entity_type' => 'node',
      'type' => 'file'
    ));
    $field_storage->save();
    FieldConfig::create([
      'entity_type' => 'node',
      'field_storage' => $field_storage,
      'bundle' => 'article',
      'label' => 'File',
    ])->save();

    // Make the field visible in the form and desfault display.
    $this->viewDisplay->load('node.article.default')
      ->setComponent('test_field')
      ->setComponent($file_field_name)
      ->save();
    $this->formDisplay->load('node.article.default')
      ->setComponent('test_field', ['type' => 'entity_reference_autocomplete'])
      ->setComponent($file_field_name, ['type' => 'file_generic'])
      ->save();

    // Create an article.
    $node = $this->drupalCreateNode([
      'type' => 'article',
      'title' => 'Test article',
    ]);

    // Upload a file to the article.
    $test_files = $this->drupalGetTestFiles('text');
    $edit['files[field_file_0]'] = $this->fileSystem->realpath($test_files['0']->uri);
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, 'Upload');
    $edit['revision'] = TRUE;
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save and keep published'));

    // Replace the file by a different one.
    $this->drupalPostForm('node/' . $node->id() . '/edit', [], 'Remove');
    $this->drupalPostForm(NULL, ['revision' => FALSE], t('Save and keep published'));
    $edit['files[field_file_0]'] = $this->fileSystem->realpath($test_files['1']->uri);
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, 'Upload');
    $edit['revision'] = TRUE;
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save and keep published'));

    // Check differences between revisions.
    $this->clickLink(t('Revisions'));
    $edit = [
      'radios_left' => 1,
      'radios_right' => 3,
    ];
    $this->drupalPostForm(NULL, $edit, t('Compare'));
    $this->assertText('File');
    $this->assertText('File: text-1.txt');
    $this->assertText('File ID: 4');

    // Use the unified fields layout.
    $this->clickLink('Unified fields');
    $this->assertResponse(200);
    $this->assertText('File');
    $this->assertText('File: text-1.txt');
    $this->assertText('File ID: 4');
  }

  /**
   * Tests the Image plugin.
   */
  public function testImagePlugin() {
    // Add image field to the article content type.
    $image_field_name = 'field_image';
    FieldStorageConfig::create([
      'field_name' => $image_field_name,
      'entity_type' => 'node',
      'type' => 'image',
      'settings' => [],
      'cardinality' => 1,
    ])->save();

    $field_config = FieldConfig::create([
      'field_name' => $image_field_name,
      'label' => 'Image',
      'entity_type' => 'node',
      'bundle' => 'article',
      'required' => FALSE,
      'settings' => ['alt_field' => 1],
    ]);
    $field_config->save();

    $this->formDisplay->load('node.article.default')
      ->setComponent($image_field_name, [
        'type' => 'image_image',
        'settings' => [],
      ])
      ->save();

    $this->viewDisplay->load('node.article.default')
      ->setComponent($image_field_name, [
        'type' => 'image',
        'settings' => [],
      ])
      ->save();

    // Create an article.
    $node = $this->drupalCreateNode([
      'type' => 'article',
      'title' => 'Test article',
    ]);

    // Upload an image to the article.
    $test_files = $this->drupalGetTestFiles('image');
    $edit = ['files[field_image_0]' => $this->fileSystem->realpath($test_files['1']->uri)];
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save and keep published'));
    $edit = [
      'field_image[0][alt]' => 'Image alt',
      'revision' => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and keep published'));

    // Replace the image by a different one.
    $this->drupalPostForm('node/' . $node->id() . '/edit', [], 'Remove');
    $this->drupalPostForm(NULL, ['revision' => FALSE], t('Save and keep published'));
    $edit = ['files[field_image_0]' => $this->fileSystem->realpath($test_files['1']->uri)];
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save and keep published'));
    $edit = [
      'field_image[0][alt]' => 'Image alt updated',
      'revision' => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and keep published'));

    // Check differences between revisions.
    $this->clickLink(t('Revisions'));
    $edit = [
      'radios_left' => 1,
      'radios_right' => 3,
    ];
    $this->drupalPostForm(NULL, $edit, t('Compare'));
    $this->assertText('Image');
    $this->assertText('Image: image-test-transparent-indexed.gif');
    $this->assertText('File ID: 2');
  }

  /**
   * Tests the Link plugin.
   */
  public function testLinkPlugin() {
    // Add a link field to the article content type.
    $field_name = 'field_link';
    $this->fieldStorage = FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
      'type' => 'link',
    ]);
    $this->fieldStorage->save();
    $this->field = FieldConfig::create([
      'field_storage' => $this->fieldStorage,
      'bundle' => 'article',
      'label' => 'Link',
      'settings' => array(
        'title' => DRUPAL_OPTIONAL,
        'link_type' => LinkItemInterface::LINK_GENERIC,
      ),
    ]);
    $this->field->save();
    $this->formDisplay->load('node.article.default')
      ->setComponent($field_name, [
        'type' => 'link_default',
        'settings' => [
          'placeholder_url' => 'http://example.com',
        ],
      ])
      ->save();
    $this->viewDisplay->load('node.article.default')
      ->setComponent($field_name, ['type' => 'link'])
      ->save();

    // Enable the comparison of the link's title field.
    $config = \Drupal::configFactory()->getEditable('diff.plugins');
    $settings['compare_title'] = TRUE;
    $config->set('fields.node.field_link.type', 'link_field_diff_builder');
    $config->set('fields.node.field_link.settings', $settings);
    $config->save();

    // Create an article, setting values on the link field.
    $node = $this->drupalCreateNode([
      'type' => 'article',
      'title' => 'Test article',
      'field_link' => [
        'title' => 'Google',
        'uri' => 'http://www.google.com',
      ],
    ]);

    // Update the link field.
    $edit = [
      'field_link[0][title]' => 'Guguel',
      'field_link[0][uri]' => 'http://www.google.es',
      'revision' => TRUE,
    ];
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save and keep published'));

    // Check differences between revisions.
    $this->clickLink(t('Revisions'));
    $this->drupalPostForm(NULL, [], t('Compare'));
    $this->assertText('Link');
    $this->assertText('Google');
    $this->assertText('http://www.google.com');
    $this->assertText('Guguel');
    $this->assertText('http://www.google.es');
  }

  /**
   * Tests the List plugin.
   */
  public function testListPlugin() {
    // Add a list field to the article content type.
    $field_name = 'field_list';
    $this->fieldStorage = FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
      'type' => 'list_string',
      'cardinality' => 1,
      'settings' => [
        'allowed_values' => [
          'value_a' => 'Value A',
          'value_b' => 'Value B',
        ],
      ],
    ]);
    $this->fieldStorage->save();

    $this->field = FieldConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
      'bundle' => 'article',
      'required' => FALSE,
      'label' => 'List',
    ])->save();

    $this->formDisplay->load('node.article.default')
      ->setComponent($field_name, ['type' => 'options_select'])
      ->save();
    $this->viewDisplay->load('node.article.default')
      ->setComponent($field_name, ['type' => 'list_default'])
      ->save();

    // Create an article, setting values on the lit field.
    $node = $this->drupalCreateNode([
      'type' => 'article',
      'title' => 'Test article',
      'field_list' => 'value_a',
    ]);

    // Update the list field.
    $edit = [
      'field_list' => 'value_b',
      'revision' => TRUE,
    ];
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save and keep published'));

    // Check differences between revisions.
    $this->clickLink(t('Revisions'));
    $this->drupalPostForm(NULL, [], t('Compare'));
    $this->assertText('List');
    $this->assertText('value_a');
    $this->assertText('value_b');
  }

  /**
   * Tests the Text plugin.
   */
  public function testTextPlugin() {
    // Add a text and a text long field to the Article content type.
    $this->addTextField('field_text', 'Text Field', 'string', 'string_textfield');
    $this->addTextField('field_text_long', 'Text Long Field', 'string_long', 'string_textarea');

    // Create an article, setting values on both fields.
    $node = $this->drupalCreateNode([
      'type' => 'article',
      'title' => 'Test article',
      'field_text' => 'Foo',
      'field_text_long' => 'Fighters',
    ]);

    // Edit the article and update these fields, creating a new revision.
    $edit = [
      'field_text[0][value]' => 'Bar',
      'field_text_long[0][value]' => 'Fly',
      'revision' => TRUE,
    ];
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save and keep published'));

    // Check differences between revisions.
    $this->clickLink(t('Revisions'));
    $this->drupalPostForm(NULL, [], t('Compare'));
    $this->assertText('Text Field');
    $this->assertText('Text Long Field');
    $this->assertText('Foo');
    $this->assertText('Fighters');
    $this->assertText('Bar');
    $this->assertText('Fly');
  }

  /**
   * Tests the access check for a field while comparing revisions.
   */
  public function testFieldNoAccess() {
    // Add a text and a text field to article.
    $this->addTextField('field_diff_deny_access', 'field_diff_deny_access', 'string', 'string_textfield');

    // Create an article.
    $this->drupalCreateNode([
      'type' => 'article',
      'title' => 'Test article access',
      'field_diff_deny_access' => 'Foo',
    ]);

    // Create a revision of the article.
    $node = $this->getNodeByTitle('Test article access');
    $node->setTitle('Test article no access');
    $node->set('field_diff_deny_access', 'Fighters');
    $node->setNewRevision(TRUE);
    $node->save();

    // Check the "Text Field No Access" field is not displayed.
    $this->drupalGet('node/'. $node->id() .'/revisions');
    $this->drupalPostForm(NULL, [], t('Compare'));
    $this->assertResponse(200);
    $this->assertNoText('field_diff_deny_access');
    $rows = $this->xpath('//tbody/tr');
    $this->assertEqual(count($rows), 2);
  }

  /**
   * Adds a text field.
   *
   * @param string $field_name
   *   The machine field name.
   * @param string $label
   *   The field label.
   * @param string $field_type.
   *   The field type.
   * @param string $widget_type.
   *   The widget type.
   */
  protected function addTextField($field_name, $label, $field_type, $widget_type) {
    // Create a field.
    $field_storage = FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
      'type' => $field_type,
    ]);
    $field_storage->save();
    FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'article',
      'label' => $label,
    ])->save();
    $this->formDisplay->load('node.article.default')
      ->setComponent($field_name, ['type' => $widget_type])
      ->save();
    $this->viewDisplay->load('node.article.default')
      ->setComponent($field_name)
      ->save();
  }

  /**
   * Tests the TextWithSummary plugin.
   */
  public function testTextWithSummaryPlugin() {
    // Enable the comparison of the summary.
    $config = \Drupal::configFactory()->getEditable('diff.plugins');
    $settings['compare_summary'] = TRUE;
    $config->set('fields.node.body.type', 'text_summary_field_diff_builder');
    $config->set('fields.node.body.settings', $settings);
    $config->save();

    // Create an article, setting the body field.
    $node = $this->drupalCreateNode([
      'type' => 'article',
      'title' => 'Test article',
      'body' => [
        'value' => 'Foo value',
        'summary' => 'Foo summary',
      ],
    ]);

    // Edit the article and update these fields, creating a new revision.
    $edit = [
      'body[0][value]' => 'Bar value',
      'body[0][summary]' => 'Bar summary',
      'revision' => TRUE,
    ];
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save and keep published'));

    // Check differences between revisions.
    $this->clickLink(t('Revisions'));
    $this->drupalPostForm(NULL, [], t('Compare'));
    $this->assertText('Body');
    $this->assertText('Foo value');
    $this->assertText('Foo summary');
    $this->assertText('Bar value');
    $this->assertText('Bar summary');
  }

  /**
   * Tests plugin applicability and weight relevance.
   */
  public function testApplicablePlugin() {
    // Add three text fields to the article.
    $this->addTextField('test_field', 'Test Applicable', 'text', 'text_textfield');
    $this->addTextField('test_field_lighter', 'Test Lighter Applicable', 'text', 'text_textfield');
    $this->addTextField('test_field_non_applicable', 'Test Not Applicable', 'text', 'text_textfield');

    // Create an article, setting values on fields.
    $node = $this->drupalCreateNode([
      'type' => 'article',
      'title' => 'Test article',
      'test_field' => 'first_nice_applicable',
      'test_field_lighter' => 'second_nice_applicable',
      'test_field_non_applicable' => 'not_applicable',
    ]);

    // Edit the article and update these fields, creating a new revision.
    $edit = [
      'test_field[0][value]' => 'first_nicer_applicable',
      'test_field_lighter[0][value]' => 'second_nicer_applicable',
      'test_field_non_applicable[0][value]' => 'nicer_not_applicable',
      'revision' => TRUE,
    ];
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save and keep published'));

    // Check differences between revisions.
    $this->clickLink(t('Revisions'));
    $this->drupalPostForm(NULL, [], t('Compare'));

    // Check diff for an applicable field of testTextPlugin.
    $this->assertText('Test Applicable');
    $this->assertText('first_nice_heavier_test_plugin');
    $this->assertText('first_nicer_heavier_test_plugin');

    // Check diff for an applicable field of testTextPlugin and
    // testLighterTextPlugin. The plugin selected for this field should be the
    // lightest one.
    $this->assertText('Test Lighter Applicable');
    $this->assertText('second_nice_lighter_test_plugin');
    $this->assertText('second_nicer_lighter_test_plugin');

    // Check diff for a non applicable field of both test plugins.
    $this->assertText('Test Not Applicable');
    $this->assertText('not_applicable');
    $this->assertText('nicer_not_applicable');
  }

}
