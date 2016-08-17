<?php

namespace Drupal\paragraphs_type_permissions\Tests;

use Drupal\Core\Entity\Entity;
use Drupal\field_ui\Tests\FieldUiTestTrait;
use Drupal\simpletest\WebTestBase;
use Drupal\user\Entity\Role;

/**
 * Tests the paragraphs type permissions.
 *
 * @group paragraphs
 */
class ParagraphsTypePermissionsTest extends WebTestBase {

  use FieldUiTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array(
    'node',
    'paragraphs_demo',
    'field',
    'image',
    'field_ui',
    'paragraphs_type_permissions',
  );

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
  }

  /**
   * Tests paragraphs type permissions for anonymous and authenticated users.
   */
  public function testAnonymousParagraphsTypePermissions() {
    // Create an authenticated user without special permissions for test.
    $authenticated_user = $this->drupalCreateUser();
    // Create an admin user for test.
    $admin_user = $this->drupalCreateUser(array(
      'administer site configuration',
      'administer nodes',
      'administer content types',
      'administer node fields',
      'administer node display',
      'administer paragraphs types',
      'administer paragraph form display',
      'create paragraphed_content_demo content',
      'edit any paragraphed_content_demo content',
      'bypass paragraphs type content access',
    ));
    $this->drupalLogin($admin_user);

    // Enable the publish/unpublish checkbox fields.
    $paragraph_types = [
      'image_text',
      'images',
      'text',
    ];
    foreach ($paragraph_types as $paragraph_type) {
      entity_get_form_display('paragraph', $paragraph_type, 'default')
        ->setComponent('status', [
          'type' => 'boolean_checkbox'
        ])
        ->save();
    }

    // Create a node with some paragraph types.
    $this->drupalGet('node/add/paragraphed_content_demo');
    $this->drupalPostForm(NULL, NULL, t('Add Image + Text'));
    $this->drupalPostForm(NULL, NULL, t('Add Images'));
    $this->drupalPostForm(NULL, NULL, t('Add Text'));

    $image_text = $this->drupalGetTestFiles('image')[0];
    $this->drupalPostForm(NULL, [
      'files[field_paragraphs_demo_0_subform_field_image_demo_0]' => $image_text->uri,
    ], t('Upload'));
    $images = $this->drupalGetTestFiles('image')[1];
    $this->drupalPostForm(NULL, [
      'files[field_paragraphs_demo_1_subform_field_images_demo_0][]' => $images->uri,
    ], t('Upload'));
    $edit = [
      'title[0][value]' => 'paragraph node title',
      'field_paragraphs_demo[0][subform][field_text_demo][0][value]' => 'Paragraph type Image + Text',
      'field_paragraphs_demo[2][subform][field_text_demo][0][value]' => 'Paragraph type Text',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save and publish');

    // Get the node to edit it later.
    $node = $this->drupalGetNodeByTitle($edit['title[0][value]']);

    // Get the images data to check for their presence.
    $image_text_tag = '/files/styles/large/public/image-test_0.png?itok=';
    $images_tag = '/files/styles/medium/public/image-test_0_0.png?itok=';

    // Check that all paragraphs are shown for admin user.
    $this->assertRaw($image_text_tag);
    $this->assertRaw($images_tag);
    $this->assertText('Paragraph type Image + Text');
    $this->assertText('Paragraph type Text');

    // Logout, check that no paragraphs are shown for anonymous user.
    $this->drupalLogout();
    $this->drupalGet('node/' . $node->id());
    $this->assertNoRaw($image_text_tag);
    $this->assertNoRaw($images_tag);
    $this->assertNoText('Paragraph type Image + Text');
    $this->assertNoText('Paragraph type Text');

    // Login as authenticated user, check that no paragraphs are shown for him.
    $this->drupalLogin($authenticated_user);
    $this->drupalGet('node/' . $node->id());
    $this->assertNoRaw($image_text_tag);
    $this->assertNoRaw($images_tag);
    $this->assertNoText('Paragraph type Image + Text');
    $this->assertNoText('Paragraph type Text');

    // Login as admin again to unpublish the 'Image + Text' paragraph type.
    $this->drupalLogout();
    $this->drupalLogin($admin_user);
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->assertFieldChecked('edit-field-paragraphs-demo-0-subform-status-value');
    $edit = [
      'field_paragraphs_demo[0][subform][status][value]' => FALSE,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and keep published'));

    // Check that 'Image + Text' paragraph is not shown anymore for admin user.
    $this->assertNoRaw($image_text_tag);
    $this->assertRaw($images_tag);
    $this->assertNoText('Paragraph type Image + Text');
    $this->assertText('Paragraph type Text');

    $this->drupalLogout();

    // Add permissions to anonymous user to view only 'Image + Text' and
    // 'Text' paragraph contents.
    /** @var \Drupal\user\RoleInterface $anonymous_role */
    $anonymous_role = Role::load('anonymous');
    $anonymous_role->grantPermission('view paragraph content image_text');
    $anonymous_role->grantPermission('view paragraph content text');
    $anonymous_role->save();

    // Add permissions to authenticated user to view only 'Image + Text' and
    // 'Images' paragraph contents.
    /** @var \Drupal\user\RoleInterface $authenticated_role */
    $authenticated_role = Role::load('authenticated');
    $authenticated_role->grantPermission('view paragraph content image_text');
    $authenticated_role->grantPermission('view paragraph content images');
    $authenticated_role->save();

    // Check that the anonymous user can only view the 'Text' paragraph.
    $this->drupalGet('node/' . $node->id());
    $this->assertNoRaw($image_text_tag);
    $this->assertNoRaw($images_tag);
    $this->assertNoText('Paragraph type Image + Text');
    $this->assertText('Paragraph type Text');

    // Check that the authenticated user can only view the 'Images' paragraph.
    $this->drupalLogin($authenticated_user);
    $this->drupalGet('node/' . $node->id());
    $this->assertNoRaw($image_text_tag);
    $this->assertRaw($images_tag);
    $this->assertNoText('Paragraph type Image + Text');
    $this->assertNoText('Paragraph type Text');
  }

}
