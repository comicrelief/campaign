<?php

/**
 * @file
 * Contains \Drupal\diff\DiffPluginTest.
 *
 * @ingroup diff
 */

namespace Drupal\diff\Tests;

use Drupal\comment\Plugin\Field\FieldType\CommentItemInterface;
use Drupal\comment\Tests\CommentTestTrait;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field_ui\Tests\FieldUiTestTrait;
use Drupal\link\LinkItemInterface;
use Drupal\simpletest\WebTestBase;

/**
 * Tests the Diff module plugins.
 *
 * @group diff
 */
class DiffPluginTest extends WebTestBase {

  use CommentTestTrait;
  use FieldUiTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node', 'diff', 'diff_test', 'block', 'comment', 'field_ui', 'file', 'image', 'link', 'options');

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create Article node type.
    $this->drupalCreateContentType(array('type' => 'article', 'name' => 'Article'));
    // Add the comment field to articles.
    $this->addDefaultCommentField('node', 'article');

    // Place the blocks that Diff module uses.
    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('local_actions_block');

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
    $this->assertText('Changes to Comments');
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
    entity_get_form_display('node', 'article', 'default')
      ->setComponent($field_name, array(
        'type' => 'email_default',
      ))
      ->save();

    // Add the email field to the default display
    entity_get_display('node', 'article', 'default')
      ->setComponent($field_name, array(
        'type' => 'basic_string',
      ))
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
    $this->assertText('Changes to Email');
    $this->assertText('foo@example.com');
    $this->assertText('bar@example.com');
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
    $this->assertText('Changes to Reference');
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
    entity_get_display('node', 'article', 'default')
      ->setComponent('test_field')
      ->setComponent($file_field_name)
      ->save();
    entity_get_form_display('node', 'article', 'default')
      ->setComponent('test_field', array(
        'type' => 'entity_reference_autocomplete',
      ))
      ->setComponent($file_field_name, array(
         'type' => 'file_generic',
      ))
      ->save();

    // Create an article.
    $node = $this->drupalCreateNode([
      'type' => 'article',
      'title' => 'Test article',
    ]);

    // Upload a file to the article.
    $test_files = $this->drupalGetTestFiles('text');
    $edit['files[field_file_0]'] = drupal_realpath($test_files['0']->uri);
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, 'Upload');
    $edit['revision'] = TRUE;
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save and keep published'));

    // Replace the file by a different one.
    $this->drupalPostForm('node/' . $node->id() . '/edit', [], 'Remove');
    $this->drupalPostForm(NULL, [], t('Save and keep published'));
    $edit['files[field_file_0]'] = drupal_realpath($test_files['1']->uri);
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
    $this->assertText('Changes to File');
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

    entity_get_form_display('node', 'article', 'default')
      ->setComponent($image_field_name, [
        'type' => 'image_image',
        'settings' => [],
      ])
      ->save();

    entity_get_display('node', 'article', 'default')
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
    $edit = ['files[field_image_0]' => drupal_realpath($test_files['1']->uri)];
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save and keep published'));
    $edit = [
      'field_image[0][alt]' => 'Image alt',
      'revision' => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and keep published'));

    // Replace the image by a different one.
    $this->drupalPostForm('node/' . $node->id() . '/edit', [], 'Remove');
    $this->drupalPostForm(NULL, [], t('Save and keep published'));
    $edit = ['files[field_image_0]' => drupal_realpath($test_files['1']->uri)];
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
    $this->assertText('Changes to Image');
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
    entity_get_form_display('node', 'article', 'default')
      ->setComponent($field_name, array(
        'type' => 'link_default',
        'settings' => [
          'placeholder_url' => 'http://example.com',
        ],
      ))
      ->save();
    entity_get_display('node', 'article', 'default')
      ->setComponent($field_name, [
        'type' => 'link',
      ])
      ->save();

    // Enable the comparison of the link's title field.
    $config = \Drupal::configFactory()->getEditable('diff.plugins');
    $settings = $config->get('field_types.link.settings');
    $settings['compare_title'] = TRUE;
    $config->set('field_types.link.settings', $settings);
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
    $this->assertText('Changes to Link');
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

    entity_get_form_display('node', 'article', 'default')
      ->setComponent($field_name, [
        'type' => 'options_select',
      ])
      ->save();
    entity_get_display('node', 'article', 'default')
      ->setComponent($field_name, [
        'type' => 'list_default',
      ])
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
    $this->assertText('Changes to List');
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
    $this->assertText('Changes to Text Field');
    $this->assertText('Changes to Text Long Field');
    $this->assertText('Foo');
    $this->assertText('Fighters');
    $this->assertText('Bar');
    $this->assertText('Fly');
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
    entity_get_form_display('node', 'article', 'default')
      ->setComponent($field_name, array(
        'type' => $widget_type,
      ))
      ->save();
    entity_get_display('node', 'article', 'default')
      ->setComponent($field_name)
      ->save();
  }

  /**
   * Tests the TextWithSummary plugin.
   */
  public function testTextWithSummaryPlugin() {
    // Enable the comparison of the summary.
    $config = \Drupal::configFactory()->getEditable('diff.plugins');
    $settings = $config->get('field_types.text_with_summary.settings');
    $settings['compare_summary'] = TRUE;
    $config->set('field_types.text_with_summary.settings', $settings);
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
    $this->assertText('Changes to Body');
    $this->assertText('Foo value');
    $this->assertText('Foo summary');
    $this->assertText('Bar value');
    $this->assertText('Bar summary');
  }

}
