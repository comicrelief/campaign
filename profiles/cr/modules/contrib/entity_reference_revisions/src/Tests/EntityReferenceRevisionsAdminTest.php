<?php
/**
 * @file
 * Contains \Drupal\entity_reference_revisions\EntityReferenceRevisionsAdminTest.
 *
 * @file
 * entity_reference_revisions configuration test functions.
 *
 * @ingroup entity_reference_revisions
 */

namespace Drupal\entity_reference_revisions\Tests;

use Drupal\field_ui\Tests\FieldUiTestTrait;
use Drupal\simpletest\WebTestBase;

/**
 * Tests the entity_reference_revisions configuration.
 *
 * @group entity_reference_revisions
 */
class EntityReferenceRevisionsAdminTest extends WebTestBase {

  use FieldUiTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array(
    'node',
    'field',
    'entity_reference_revisions',
    'field_ui',
    'block',
  );

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    // Create paragraphs and article content types.
    $this->drupalCreateContentType(array('type' => 'entity_revisions', 'name' => 'Entity revisions'));
    $this->drupalCreateContentType(array('type' => 'article', 'name' => 'Article'));
    // Place the breadcrumb, tested in fieldUIAddNewField().
    $this->drupalPlaceBlock('system_breadcrumb_block');
  }

  /**
   * Tests the entity reference revisions configuration.
   */
  public function testEntityReferenceRevisions() {
    $admin_user = $this->drupalCreateUser(array(
      'administer site configuration',
      'administer nodes',
      'create article content',
      'administer content types',
      'administer node fields',
      'administer node display',
      'administer node form display',
      'edit any article content',
    ));
    $this->drupalLogin($admin_user);
    // Create entity reference revisions field.
    static::fieldUIAddNewField('admin/structure/types/manage/entity_revisions', 'entity_reference_revisions', 'Entity reference revisions', 'entity_reference_revisions', array('settings[target_type]' => 'node', 'cardinality' => '-1'), array('settings[handler_settings][target_bundles][article]' => TRUE));
    $this->assertText('Saved Entity reference revisions configuration.');

    // Create an article.
    $title = $this->randomMachineName();
    $edit = array(
      'title[0][value]' => $title,
      'body[0][value]' => 'Revision 1',
      'revision' => TRUE,
    );
    $this->drupalPostForm('node/add/article', $edit, t('Save and publish'));
    $this->assertText($title);
    $this->assertText('Revision 1');
    $node = $this->drupalGetNodeByTitle($title);

    // Create entity revisions content that includes the above article.
    $edit = array(
      'title[0][value]' => 'Entity reference revision content',
      'field_entity_reference_revisions[0][target_id]' => $node->label() . ' (' . $node->id() . ')',
    );
    $this->drupalPostForm('node/add/entity_revisions', $edit, t('Save and publish'));
    $this->assertText('Entity revisions Entity reference revision content has been created.');
    $this->assertText('Entity reference revision content');
    $this->assertText($title);
    $this->assertText('Revision 1');

    // Create 2nd revision of the article.
    $edit = array(
      'body[0][value]' => 'Revision 2',
      'revision' => TRUE,
    );
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save and keep published'));
    $this->assertText($title);
    $this->assertText('Revision 2');

    // View the Entity reference content and make sure it still has revision 1.
    $node = $this->drupalGetNodeByTitle('Entity reference revision content');
    $this->drupalGet('node/' . $node->id());
    $this->assertText($title);
    $this->assertText('Revision 1');
    $this->assertNoText('Revision 2');

    // Make sure the non-revisionable entities are not selectable as referenced.
    // entities.
    $edit = array(
      'new_storage_type' => 'entity_reference_revisions',
      'label' => 'Entity reference revisions field',
      'field_name' => 'entity_ref_revisions_field',
    );
    $this->drupalPostForm('admin/structure/types/manage/entity_revisions/fields/add-field', $edit, t('Save and continue'));
    $this->assertNoOption('edit-settings-target-type', 'user');
    $this->assertOption('edit-settings-target-type', 'node');
  }

}
