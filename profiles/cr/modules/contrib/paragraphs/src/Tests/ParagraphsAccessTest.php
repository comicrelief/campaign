<?php

namespace Drupal\paragraphs\Tests;

use Drupal\Core\Entity\Entity;
use Drupal\field_ui\Tests\FieldUiTestTrait;
use Drupal\simpletest\WebTestBase;
use Drupal\user\RoleInterface;
use Drupal\user\Entity\Role;

/**
 * Tests the access check of paragraphs.
 *
 * @group paragraphs
 */
class ParagraphsAccessTest extends WebTestBase {

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
    'paragraphs_demo',
  );

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('page_title_block');
  }

  /**
   * Tests the paragraph translation.
   */
  public function testParagraphAccessCheck() {
    $admin_user = $this->drupalCreateUser(array(
      'administer site configuration',
      'administer nodes',
      'administer content types',
      'administer node fields',
      'administer node display',
      'administer paragraphs types',
      'administer paragraph fields',
      'administer paragraph display',
      'administer paragraph form display',
      'administer node form display',
      'create paragraphed_content_demo content',
      'edit any paragraphed_content_demo content',
    ));
    $this->drupalLogin($admin_user);

    $this->drupalLogin($admin_user);

    // Remove the "access content" for anonymous users. That results in
    // anonymous users not being able to "view" the host entity.
    /* @var Role $role */
    $role = \Drupal::entityTypeManager()
      ->getStorage('user_role')
      ->load(RoleInterface::ANONYMOUS_ID);
    $role->revokePermission('access content');
    $role->save();

    // Set field_images from demo to private file storage.
    $edit = array(
      'settings[uri_scheme]' => 'private',
    );
    $this->drupalPostForm('admin/structure/paragraphs_type/images/fields/paragraph.images.field_images_demo/storage', $edit, t('Save field settings'));

    // Create a new demo node.
    $this->drupalGet('node/add/paragraphed_content_demo');

    // Add a new paragraphs images item.
    $this->drupalPostForm(NULL, NULL, t('Add Images'));

    // Create a file, upload it.
    $text = 'Trust me I\'m an image';
    file_put_contents('temporary://privateImage.jpg', $text);
    $file_path = $this->container->get('file_system')
      ->realpath('temporary://privateImage.jpg');

    // Create a file, upload it.
    $text = 'Trust me I\'m an image 2';
    file_put_contents('temporary://privateImage2.jpg', $text);
    $file_path_2 = $this->container->get('file_system')
      ->realpath('temporary://privateImage2.jpg');

    $edit = array(
      'title[0][value]' => 'Security test node',
      'files[field_paragraphs_demo_0_subform_field_images_demo_0][]' => [$file_path, $file_path_2],
    );

    $this->drupalPostForm(NULL, $edit, t('Preview'));
    $img1_url = file_create_url(\Drupal::token()->replace('private://privateImage.jpg'));
    $image_url = file_url_transform_relative($img1_url);
    $this->assertRaw($image_url, 'Image was found in preview');
    $this->clickLink(t('Back to content editing'));
    $edit = [
      'field_paragraphs_demo[0][subform][field_images_demo][0][width]' => 100,
      'field_paragraphs_demo[0][subform][field_images_demo][0][height]' => 100,
      'field_paragraphs_demo[0][subform][field_images_demo][1][width]' => 100,
      'field_paragraphs_demo[0][subform][field_images_demo][1][height]' => 100,
    ];
    $this->drupalPostForm(NULL, $edit, 'Save and publish');

    $node = $this->drupalGetNodeByTitle('Security test node');

    $this->drupalGet('node/' . $node->id());

    // Check the text and image after publish.
    $this->assertRaw($image_url, 'Image was found in content');

    $this->drupalGet($image_url);
    $this->assertResponse(200, 'Image could be downloaded');

    // Logout to become anonymous.
    $this->drupalLogout();

    // @todo Requesting the same $img_url again triggers a caching problem on
    // drupal.org test bot, thus we request a different file here.
    $img_url = file_create_url(\Drupal::token()->replace('private://privateImage2.jpg'));
    $image_url = file_url_transform_relative($img_url);
    // Check the text and image after publish. Anonymous should not see content.
    $this->assertNoRaw($image_url, 'Image was not found in content');

    $this->drupalGet($image_url);
    $this->assertResponse(403, 'Image could not be downloaded');

    // Login as admin with no delete permissions.
    $this->drupalLogin($admin_user);
    // Create a new demo node.
    $this->drupalGet('node/add/paragraphed_content_demo');
    $this->drupalPostForm(NULL, NULL, t('Add Text'));
    $this->assertText('Type: Text');
    $edit = [
      'title[0][value]' => 'delete_permissions',
      'field_paragraphs_demo[0][subform][field_text_demo][0][value]' => 'Test',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save and publish');
    // Edit the node.
    $this->clickLink(t('Edit'));
    // Check the remove button is present.
    $this->assertNotNull($this->xpath('//*[@name="field_paragraphs_demo_0_remove"]'));
    // Delete the Paragraph and save.
    $this->drupalPostAjaxForm(NULL, [], 'field_paragraphs_demo_0_remove');
    $this->drupalPostAjaxForm(NULL, [], 'field_paragraphs_demo_0_confirm_remove');
    $this->drupalPostForm(NULL, [], t('Save and keep published'));
    $node = $this->getNodeByTitle('delete_permissions');
    $this->assertUrl('node/' . $node->id());
  }
}
