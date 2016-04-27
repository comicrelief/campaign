<?php

/**
 * @file
 * Contains \Drupal\field\Tests\EntityReference\EntityReferenceAutoCreateTest.
 */

namespace Drupal\field\Tests\EntityReference;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\simpletest\WebTestBase;
use Drupal\node\Entity\Node;

/**
 * Tests creating new entity (e.g. taxonomy-term) from an autocomplete widget.
 *
 * @group entity_reference
 */
class EntityReferenceAutoCreateTest extends WebTestBase {

  public static $modules = ['node'];

  /**
   * The name of a content type that will reference $referencedType.
   *
   * @var string
   */
  protected $referencingType;

  /**
   * The name of a content type that will be referenced by $referencingType.
   *
   * @var string
   */
  protected $referencedType;

  protected function setUp() {
    parent::setUp();

    // Create "referencing" and "referenced" node types.
    $referencing = $this->drupalCreateContentType();
    $this->referencingType = $referencing->id();

    $referenced = $this->drupalCreateContentType();
    $this->referencedType = $referenced->id();

    entity_create('field_storage_config', array(
      'field_name' => 'test_field',
      'entity_type' => 'node',
      'translatable' => FALSE,
      'entity_types' => array(),
      'settings' => array(
        'target_type' => 'node',
      ),
      'type' => 'entity_reference',
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
    ))->save();

    entity_create('field_config', array(
      'label' => 'Entity reference field',
      'field_name' => 'test_field',
      'entity_type' => 'node',
      'bundle' => $referencing->id(),
      'settings' => array(
        'handler' => 'default',
        'handler_settings' => array(
          // Reference a single vocabulary.
          'target_bundles' => array(
            $referenced->id(),
          ),
          // Enable auto-create.
          'auto_create' => TRUE,
        ),
      ),
    ))->save();

    entity_get_display('node', $referencing->id(), 'default')
      ->setComponent('test_field')
      ->save();
    entity_get_form_display('node', $referencing->id(), 'default')
      ->setComponent('test_field', array(
        'type' => 'entity_reference_autocomplete',
      ))
      ->save();
  }

  /**
   * Tests that the autocomplete input element appears and the creation of a new
   * entity.
   */
  public function testAutoCreate() {
    $user1 = $this->drupalCreateUser(array('access content', "create $this->referencingType content"));
    $this->drupalLogin($user1);

    $this->drupalGet('node/add/' . $this->referencingType);
    $this->assertFieldByXPath('//input[@id="edit-test-field-0-target-id" and contains(@class, "form-autocomplete")]', NULL, 'The autocomplete input element appears.');

    $new_title = $this->randomMachineName();

    // Assert referenced node does not exist.
    $base_query = \Drupal::entityQuery('node');
    $base_query
      ->condition('type', $this->referencedType)
      ->condition('title', $new_title);

    $query = clone $base_query;
    $result = $query->execute();
    $this->assertFalse($result, 'Referenced node does not exist yet.');

    $edit = array(
      'title[0][value]' => $this->randomMachineName(),
      'test_field[0][target_id]' => $new_title,
    );
    $this->drupalPostForm("node/add/$this->referencingType", $edit, 'Save');

    // Assert referenced node was created.
    $query = clone $base_query;
    $result = $query->execute();
    $this->assertTrue($result, 'Referenced node was created.');
    $referenced_nid = key($result);
    $referenced_node = Node::load($referenced_nid);

    // Assert the referenced node is associated with referencing node.
    $result = \Drupal::entityQuery('node')
      ->condition('type', $this->referencingType)
      ->execute();

    $referencing_nid = key($result);
    $referencing_node = Node::load($referencing_nid);
    $this->assertEqual($referenced_nid, $referencing_node->test_field->target_id, 'Newly created node is referenced from the referencing node.');

    // Now try to view the node and check that the referenced node is shown.
    $this->drupalGet('node/' . $referencing_node->id());
    $this->assertText($referencing_node->label(), 'Referencing node label found.');
    $this->assertText($referenced_node->label(), 'Referenced node label found.');
  }
}
