<?php

namespace Drupal\Tests\entity_reference_revisions\Kernel;

use Drupal\entity_composite_relationship_test\Entity\EntityTestCompositeRelationship;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Tests the entity_reference_revisions NeedsSaveInterface.
 *
 * @group entity_reference_revisions
 */
class EntityReferenceRevisionsSaveTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array(
    'node',
    'user',
    'system',
    'field',
    'entity_reference_revisions',
    'entity_composite_relationship_test',
  );

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    // Create article content type.
    $values = ['type' => 'article', 'name' => 'Article'];
    $node_type = NodeType::create($values);
    $node_type->save();
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('entity_test_composite');
    $this->installSchema('system', ['sequences']);
    $this->installSchema('node', ['node_access']);
  }

  /**
   * Test for NeedsSaveInterface implementation.
   *
   * Tests that the referenced entity is saved when needsSave() is TRUE.
   */
  public function testNeedsSave() {

    // Add the entity_reference_revisions field to article.
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

    $text = 'Dummy text';
    // Create the test composite entity.
    $entity_test = EntityTestCompositeRelationship::create(array(
      'uuid' => $text,
      'name' => $text,
    ));
    $entity_test->save();

    $text = 'Clever text';
    // Set the name to a new text.
    /** @var \Drupal\entity_composite_relationship_test\Entity\EntityTestCompositeRelationship $entity_test */
    $entity_test->name = $text;
    $entity_test->setNeedsSave(TRUE);
    // Create a node with a reference to the test entity and save.
    $node = Node::create([
      'title' => $this->randomMachineName(),
      'type' => 'article',
      'composite_reference' => $entity_test,
    ]);
    // Check the name is properly set.
    $values = $node->composite_reference->getValue();
    $this->assertTrue(isset($values[0]['entity']));
    static::assertEquals($values[0]['entity']->name->value, $text);
    $node->composite_reference->setValue($values);
    static::assertEquals($node->composite_reference->entity->name->value, $text);
    $node->save();

    // Check that the name has been updated when the parent has been saved.
    /** @var \Drupal\entity_composite_relationship_test\Entity\EntityTestCompositeRelationship $entity_test_after */
    $entity_test_after = EntityTestCompositeRelationship::load($entity_test->id());
    static::assertEquals($entity_test_after->name->value, $text);

    $new_text = 'Dummy text again';
    // Set the name again.
    $entity_test->name = $new_text;
    $entity_test->setNeedsSave(FALSE);

    // Load the Node and check the composite reference field is not set.
    $node = Node::load($node->id());
    $values = $node->composite_reference->getValue();
    $this->assertFalse(isset($values[0]['entity']));
    $node->composite_reference = $entity_test;
    $node->save();

    // Check the name is not updated.
    $entity_test_after = EntityTestCompositeRelationship::load($entity_test->id());
    static::assertEquals($entity_test_after->name->value, $text);

    // Test if after delete the referenced entity there are no problems setting
    // the referencing values to the parent.
    $entity_test->delete();
    $node = Node::load($node->id());
    $node->save();
  }

  /**
   * Test for NeedsSaveInterface implementation.
   *
   * Tests that the fields in the parent are properly updated.
   */
  public function testSaveNewEntity() {
    // Add the entity_reference_revisions field to article.
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

    $text = 'Dummy text';
    // Create the test entity.
    $entity_test = EntityTestCompositeRelationship::create(array(
      'uuid' => $text,
      'name' => $text,
    ));

    // Create a node with a reference to the test entity and save.
    $node = Node::create([
      'title' => $this->randomMachineName(),
      'type' => 'article',
      'composite_reference' => $entity_test,
    ]);
    $validate = $node->validate();
    $this->assertEmpty($validate);
    $node->save();

    // Test that the fields on node are properly set.
    $node_after = Node::load($node->id());
    static::assertEquals($node_after->composite_reference[0]->target_id, $entity_test->id());
    static::assertEquals($node_after->composite_reference[0]->target_revision_id, $entity_test->getRevisionId());
    // Check that the entity is not new after save parent.
    $this->assertFalse($entity_test->isNew());

    // Create a new test entity.
    $text = 'Smart text';
    $second_entity_test = EntityTestCompositeRelationship::create(array(
      'uuid' => $text,
      'name' => $text,
    ));
    $second_entity_test->save();

    // Set the new test entity to the node field.
    $node_after->composite_reference = $second_entity_test;
    // Check the fields have been updated.
    static::assertEquals($node_after->composite_reference[0]->target_id, $second_entity_test->id());
    static::assertEquals($node_after->composite_reference[0]->target_revision_id, $second_entity_test->getRevisionId());
  }
}
