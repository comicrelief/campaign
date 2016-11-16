<?php

namespace Drupal\entity_reference_revisions\Tests;

use Drupal\entity_composite_relationship_test\Entity\EntityTestCompositeRelationship;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\simpletest\WebTestBase;

/**
 * Tests the entity_reference_revisions composite relationship.
 *
 * @group entity_reference_revisions
 */
class EntityReferenceRevisionsCompositeTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array(
    'block_content',
    'node',
    'field',
    'entity_reference_revisions',
    'field_ui',
    'entity_composite_relationship_test',
  );

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    // Create article content type.
    $this->drupalCreateContentType(array('type' => 'article', 'name' => 'Article'));
  }

  /**
   * Test for maintaining composite relationship.
   *
   * Tests that the referenced entity saves the parent type and id when saving.
   */
  public function testEntityReferenceRevisionsCompositeRelationship() {

    // Create the reference to the composite entity test.
    $field_storage = FieldStorageConfig::create(array(
      'field_name' => 'composite_reference',
      'entity_type' => 'node',
      'type' => 'entity_reference_revisions',
      'settings' => array(
        'target_type' => 'entity_test_composite'
      ),
    ));
    $field_storage->save();
    $field = FieldConfig::create(array(
      'field_storage' => $field_storage,
      'bundle' => 'article',
    ));
    $field->save();

    // Create the test composite entity.
    $composite = EntityTestCompositeRelationship::create(array(
      'uuid' => $this->randomMachineName(),
      'name' => $this->randomMachineName(),
    ));
    $composite->save();

    // Create a node with a reference to the test composite entity.
    $node = $this->createNode(array(
      'title' => $this->randomMachineName(),
      'type' => 'article',
      'composite_reference' => $composite,
    ));

    // Verify the value of parent type and id after create a node.
    $composite_after = EntityTestCompositeRelationship::load($composite->id())->toArray();
    $this->assertEqual($composite_after['parent_type'][0]['value'], $node->getEntityTypeId());
    $this->assertEqual($composite_after['parent_id'][0]['value'], $node->id());
    $this->assertEqual($composite_after['parent_field_name'][0]['value'], 'composite_reference');

    // Create 2nd revision of the node.
    $original_composite_revision = $node->composite_reference[0]->target_revision_id;
    $original_node_revision = $node->getRevisionId();
    $node->setTitle('2nd revision');
    $node->setNewRevision();
    $node->save();
    $node = node_load($node->id(), TRUE);
    $this->assertEqual('2nd revision', $node->getTitle(), 'New node revision has changed data.');
    $this->assertNotEqual($original_composite_revision, $node->composite_reference[0]->target_revision_id, 'Composite entity got new revision when its host did.');

    // Revert to first revision of the node.
    $node = \Drupal::entityTypeManager()->getStorage('node')->loadRevision($original_node_revision);
    $node->setNewRevision();
    $node->isDefaultRevision(TRUE);
    $node->save();
    $node = node_load($node->id(), TRUE);
    $this->assertNotEqual('2nd revision', $node->getTitle(), 'Node did not keep changed title after reversion.');
    $this->assertNotEqual($original_composite_revision, $node->composite_reference[0]->target_revision_id, 'Composite entity got new revision when its host reverted to an old revision.');

    // Test that the composite entity is deleted when its parent is deleted.
    $node->delete();
    $this->assertNull(EntityTestCompositeRelationship::load($composite->id()));
  }

}
