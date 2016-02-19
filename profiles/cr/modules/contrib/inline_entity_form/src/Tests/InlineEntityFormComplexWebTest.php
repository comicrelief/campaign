<?php

/**
 * @file
 * Contains \Drupal\inline_entity_form\Tests\InlineEntityFormComplexWebTest.
 */

namespace Drupal\inline_entity_form\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * IEF complex field widget tests.
 *
 * @group inline_entity_form
 */
class InlineEntityFormComplexWebTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'inline_entity_form_test',
    'field',
    'field_ui',
  ];

  /**
   * User with permissions to create content.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $user;

  /*
   * URL to add new content.
   *
   * @var string
   */
  protected $formContentAddUrl;

  /**
   * Entity form display storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface
   */
  protected $entityFormDisplayStorage;

  /**
   * Prepares environment for
   */
  protected function setUp() {
    parent::setUp();

    $this->user = $this->createUser([
      'create ief_reference_type content',
      'edit any ief_reference_type content',
      'delete any ief_reference_type content',
      'create ief_test_complex content',
      'edit any ief_test_complex content',
      'delete any ief_test_complex content',
      'view own unpublished content',
      'administer content types',
      'administer node fields',
    ]);
    $this->drupalLogin($this->user);

    $this->formContentAddUrl = 'node/add/ief_test_complex';
    $this->entityFormDisplayStorage = $this->container->get('entity.manager')->getStorage('entity_form_display');

  }

  /**
   * Tests if form behaves correctly when field is empty.
   */
  public function testEmptyFieldIEF() {
    // Don't allow addition of existing nodes.
    $this->setAllowExisting(FALSE);
    $this->drupalGet($this->formContentAddUrl);

    $this->assertFieldByName('multi[form][inline_entity_form][title][0][value]', NULL, 'Title field on inline form exists.');
    $this->assertFieldByName('multi[form][inline_entity_form][first_name][0][value]', NULL, 'First name field on inline form exists.');
    $this->assertFieldByName('multi[form][inline_entity_form][last_name][0][value]', NULL, 'Last name field on inline form exists.');
    $this->assertFieldByXpath('//input[@type="submit" and @value="Create node"]', NULL, 'Found "Create node" submit button');

    // Allow addition of existing nodes.
    $this->setAllowExisting(TRUE);
    $this->drupalGet($this->formContentAddUrl);

    $this->assertNoFieldByName('multi[form][inline_entity_form][title][0][value]', NULL, 'Title field does not appear.');
    $this->assertNoFieldByName('multi[form][inline_entity_form][first_name][0][value]', NULL, 'First name field does not appear.');
    $this->assertNoFieldByName('multi[form][inline_entity_form][last_name][0][value]', NULL, 'Last name field does not appear.');
    $this->assertFieldByXpath('//input[@type="submit" and @value="Add new node"]', NULL, 'Found "Add new node" submit button');
    $this->assertFieldByXpath('//input[@type="submit" and @value="Add existing node"]', NULL, 'Found "Add existing node" submit button');

    // Now submit 'Add new node' button.
    $this->drupalPostAjaxForm(NULL, [], $this->getButtonName('//input[@type="submit" and @value="Add new node" and @data-drupal-selector="edit-multi-actions-ief-add"]'));

    $this->assertFieldByName('multi[form][inline_entity_form][title][0][value]', NULL, 'Title field on inline form exists.');
    $this->assertFieldByName('multi[form][inline_entity_form][first_name][0][value]', NULL, 'First name field on inline form exists.');
    $this->assertFieldByName('multi[form][inline_entity_form][last_name][0][value]', NULL, 'Second name field on inline form exists.');
    $this->assertFieldByXpath('//input[@type="submit" and @value="Create node"]', NULL, 'Found "Create node" submit button');
    $this->assertFieldByXpath('//input[@type="submit" and @value="Cancel"]', NULL, 'Found "Cancel" submit button');

    // Now submit 'Add Existing node' button.
    $this->drupalGet($this->formContentAddUrl);
    $this->drupalPostAjaxForm(NULL, [], $this->getButtonName('//input[@type="submit" and @value="Add existing node" and @data-drupal-selector="edit-multi-actions-ief-add-existing"]'));

    $this->assertFieldByName('multi[form][entity_id]', NULL, 'Existing entity reference autocomplete field found.');
    $this->assertFieldByXpath('//input[@type="submit" and @value="Add node"]', NULL, 'Found "Add node" submit button');
    $this->assertFieldByXpath('//input[@type="submit" and @value="Cancel"]', NULL, 'Found "Cancel" submit button');
  }

  /**
   * Tests creation of entities.
   */
  public function testEntityCreation() {
    // Allow addition of existing nodes.
    $this->setAllowExisting(TRUE);
    $this->drupalGet($this->formContentAddUrl);

    $this->drupalPostAjaxForm(NULL, [], $this->getButtonName('//input[@type="submit" and @value="Add new node" and @data-drupal-selector="edit-multi-actions-ief-add"]'));
    $this->assertResponse(200, 'Opening new inline form was successful.');

    $this->drupalPostAjaxForm(NULL, [], $this->getButtonName('//input[@type="submit" and @value="Create node" and @data-drupal-selector="edit-multi-form-inline-entity-form-actions-ief-add-save"]'));
    $this->assertResponse(200, 'Submitting empty form was successful.');
    $this->assertText('First name field is required.', 'Validation failed for empty "First name" field.');
    $this->assertText('Last name field is required.', 'Validation failed for empty "Last name" field.');
    $this->assertText('Title field is required.', 'Validation failed for empty "Title" field.');

    // Create ief_reference_type node in IEF.
    $this->drupalGet($this->formContentAddUrl);
    $this->drupalPostAjaxForm(NULL, [], $this->getButtonName('//input[@type="submit" and @value="Add new node" and @data-drupal-selector="edit-multi-actions-ief-add"]'));
    $this->assertResponse(200, 'Opening new inline form was successful.');

    $edit = [
      'multi[form][inline_entity_form][title][0][value]' => 'Some reference',
      'multi[form][inline_entity_form][first_name][0][value]' => 'John',
      'multi[form][inline_entity_form][last_name][0][value]' => 'Doe',
    ];
    $this->drupalPostAjaxForm(NULL, $edit, $this->getButtonName('//input[@type="submit" and @value="Create node" and @data-drupal-selector="edit-multi-form-inline-entity-form-actions-ief-add-save"]'));
    $this->assertResponse(200, 'Creating node via inline form was successful.');

    // Tests if correct fields appear in the table.
    $this->assertTrue((bool) $this->xpath('//td[@class="inline-entity-form-node-label" and contains(.,"Some reference")]'), 'Node title field appears in the table');
    $this->assertTrue((bool) $this->xpath('//td[@class="inline-entity-form-node-status" and ./div[contains(.,"Published")]]'), 'Node status field appears in the table');

    // Tests if edit and remove buttons appear.
    $this->assertTrue((bool) $this->xpath('//input[@type="submit" and @value="Edit"]'), 'Edit button appears in the table.');
    $this->assertTrue((bool) $this->xpath('//input[@type="submit" and @value="Remove"]'), 'Remove button appears in the table.');

    // Test edit functionality.
    $this->drupalPostAjaxForm(NULL, [], $this->getButtonName('//input[@type="submit" and @value="Edit"]'));
    $edit = [
      'multi[form][inline_entity_form][entities][0][form][title][0][value]' => 'Some changed reference',
    ];
    $this->drupalPostAjaxForm(NULL, $edit, $this->getButtonName('//input[@type="submit" and @value="Update node"]'));
    $this->assertTrue((bool) $this->xpath('//td[@class="inline-entity-form-node-label" and contains(.,"Some changed reference")]'), 'Node title field appears in the table');
    $this->assertTrue((bool) $this->xpath('//td[@class="inline-entity-form-node-status" and ./div[contains(.,"Published")]]'), 'Node status field appears in the table');

    // Create ief_test_complex node.
    $edit = ['title[0][value]' => 'Some title'];
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertResponse(200, 'Saving parent entity was successful.');

    // Checks values of created entities.
    $node = $this->drupalGetNodeByTitle('Some changed reference');
    $this->assertTrue($node, 'Created ief_reference_type node ' . $node->label());
    $this->assertTrue($node->get('first_name')->value == 'John', 'First name in reference node set to John');
    $this->assertTrue($node->get('last_name')->value == 'Doe', 'Last name in reference node set to Doe');

    $parent_node = $this->drupalGetNodeByTitle('Some title');
    $this->assertTrue($parent_node, 'Created ief_test_complex node ' . $parent_node->label());
    $this->assertTrue($parent_node->multi->target_id == $node->id(), 'Refererence node id set to ' . $node->id());
  }

  /**
   * Tests if editing and removing entities work.
   */
  public function testEntityEditingAndRemoving() {
    // Allow addition of existing nodes.
    $this->setAllowExisting(TRUE);

    // Create three ief_reference_type entities.
    $referenceNodes = $this->createReferenceContent(3);
    $this->drupalCreateNode([
      'type' => 'ief_test_complex',
      'title' => 'Some title',
      'multi' => array_values($referenceNodes),
    ]);
    /** @var \Drupal\node\NodeInterface $node */
    $parent_node = $this->drupalGetNodeByTitle('Some title');

    // Edit the second entity.
    $this->drupalGet('node/'. $parent_node->id() .'/edit');
    $cell = $this->xpath('//table[@id="ief-entity-table-edit-multi-entities"]/tbody/tr[@class="ief-row-entity draggable even"]/td[@class="inline-entity-form-node-label"]');
    $title = (string) $cell[0];

    $this->drupalPostAjaxForm(NULL, [], $this->getButtonName('//input[@type="submit" and @id="edit-multi-entities-1-actions-ief-entity-edit"]'));
    $this->assertResponse(200, 'Opening inline edit form was successful.');

    $edit = [
      'multi[form][inline_entity_form][entities][1][form][first_name][0][value]' => 'John',
      'multi[form][inline_entity_form][entities][1][form][last_name][0][value]' => 'Doe',
    ];
    $this->drupalPostAjaxForm(NULL, $edit, $this->getButtonName('//input[@type="submit" and @data-drupal-selector="edit-multi-form-inline-entity-form-entities-1-form-actions-ief-edit-save"]'));
    $this->assertResponse(200, 'Saving inline edit form was successful.');

    // Save the ief_test_complex node.
    $this->drupalPostForm(NULL, [], t('Save'));
    $this->assertResponse(200, 'Saving parent entity was successful.');

    // Checks values of changed entities.
    $node = $this->drupalGetNodeByTitle($title, TRUE);
    $this->assertTrue($node->first_name->value == 'John', 'First name in reference node changed to John');
    $this->assertTrue($node->last_name->value == 'Doe', 'Last name in reference node changed to Doe');

    // Delete the second entity.
    $this->drupalGet('node/'. $parent_node->id() .'/edit');
    $cell = $this->xpath('//table[@id="ief-entity-table-edit-multi-entities"]/tbody/tr[@class="ief-row-entity draggable even"]/td[@class="inline-entity-form-node-label"]');
    $title = (string) $cell[0];

    $this->drupalPostAjaxForm(NULL, [], $this->getButtonName('//input[@type="submit" and @id="edit-multi-entities-1-actions-ief-entity-remove"]'));
    $this->assertResponse(200, 'Opening inline remove confirm form was successful.');
    $this->assertText('Are you sure you want to remove', 'Remove warning message is displayed.');

    $this->drupalPostAjaxForm(NULL, ['multi[form][entities][1][form][delete]' => TRUE], $this->getButtonName('//input[@type="submit" and @data-drupal-selector="edit-multi-form-entities-1-form-actions-ief-remove-confirm"]'));
    $this->assertResponse(200, 'Removing inline entity was successful.');
    $this->assertNoText($title, 'Deleted inline entity is not present on the page.');

    // Save the ief_test_complex node.
    $this->drupalPostForm(NULL, [], t('Save'));
    $this->assertResponse(200, 'Saving parent node was successful.');

    // Checks that entity does nor appear in IEF.
    $this->drupalGet('node/'. $parent_node->id() .'/edit');
    $this->assertNoText($title, 'Deleted inline entity is not present on the page after saving parent.');

    // Delete the third entity reference only, don't delete the node. The third
    // entity now is second referenced entity because the second one was deleted
    // in previous step.
    $this->drupalGet('node/'. $parent_node->id() .'/edit');
    $cell = $this->xpath('//table[@id="ief-entity-table-edit-multi-entities"]/tbody/tr[@class="ief-row-entity draggable even"]/td[@class="inline-entity-form-node-label"]');
    $title = (string) $cell[0];

    $this->drupalPostAjaxForm(NULL, [], $this->getButtonName('//input[@type="submit" and @id="edit-multi-entities-1-actions-ief-entity-remove"]'));
    $this->assertResponse(200, 'Opening inline remove confirm form was successful.');

    $this->drupalPostAjaxForm(NULL, [], $this->getButtonName('//input[@type="submit" and @data-drupal-selector="edit-multi-form-entities-1-form-actions-ief-remove-confirm"]'));
    $this->assertResponse(200, 'Removing inline entity was successful.');

    // Save the ief_test_complex node.
    $this->drupalPostForm(NULL, [], t('Save'));
    $this->assertResponse(200, 'Saving parent node was successful.');

    // Checks that entity does nor appear in IEF.
    $this->drupalGet('node/'. $parent_node->id() . '/edit');
    $this->assertNoText($title, 'Deleted inline entity is not present on the page after saving parent.');

    // Checks that entity is not deleted.
    $node = $this->drupalGetNodeByTitle($title, TRUE);
    $this->assertTrue($node, 'Reference node not deleted');
  }

  /**
   * Tests if referencing existing entities work.
   */
  public function testReferencingExistingEntities() {
    // Allow addition of existing nodes.
    $this->setAllowExisting(TRUE);

    // Create three ief_reference_type entities.
    $referenceNodes = $this->createReferenceContent(3);

    // Create a node for every bundle available.
    $bundle_nodes = $this->createNodeForEveryBundle();

    // Create ief_test_complex node with first ief_reference_type node and first
    // node from bundle nodes.
    $this->drupalCreateNode([
      'type' => 'ief_test_complex',
      'title' => 'Some title',
      'multi' => [1],
      'all_bundles' => key($bundle_nodes),
    ]);
    // Remove first node since we already added it.
    unset($bundle_nodes[key($bundle_nodes)]);

    $parent_node = $this->drupalGetNodeByTitle('Some title', TRUE);

    // Add remaining existing reference nodes.
    $this->drupalGet('node/' . $parent_node->id() . '/edit');
    for ($i = 2; $i <= 3; $i++) {
      $this->drupalPostAjaxForm(NULL, [], $this->getButtonName('//input[@type="submit" and @value="Add existing node" and @data-drupal-selector="edit-multi-actions-ief-add-existing"]'));
      $this->assertResponse(200, 'Opening reference form was successful.');
      $title = 'Some reference ' . $i;
      $edit = [
        'multi[form][entity_id]' => $title . ' (' . $referenceNodes[$title] . ')',
      ];
      $this->drupalPostAjaxForm(NULL, $edit, $this->getButtonName('//input[@type="submit" and @data-drupal-selector="edit-multi-form-actions-ief-reference-save"]'));
      $this->assertResponse(200, 'Adding new referenced entity was successful.');
    }
    // Add all remaining nodes from all bundles.
    foreach ($bundle_nodes as $id => $title) {
      $this->drupalPostAjaxForm(NULL, [], $this->getButtonName('//input[@type="submit" and @value="Add existing node" and @data-drupal-selector="edit-all-bundles-actions-ief-add-existing"]'));
      $this->assertResponse(200, 'Opening reference form was successful.');
      $edit = [
        'all_bundles[form][entity_id]' => $title . ' (' . $id . ')',
      ];
      $this->drupalPostAjaxForm(NULL, $edit, $this->getButtonName('//input[@type="submit" and @data-drupal-selector="edit-all-bundles-form-actions-ief-reference-save"]'));
      $this->assertResponse(200, 'Adding new referenced entity was successful.');
    }
    // Save the node.
    $this->drupalPostForm(NULL, [], t('Save'));
    $this->assertResponse(200, 'Saving parent for was successful.');

    // Check if entities are referenced.
    $this->drupalGet('node/'. $parent_node->id() .'/edit');
    for ($i = 2; $i <= 3; $i++) {
      $cell = $this->xpath('//table[@id="ief-entity-table-edit-multi-entities"]/tbody/tr[' . $i . ']/td[@class="inline-entity-form-node-label"]');
      $this->assertTrue($cell[0] == 'Some reference ' . $i, 'Found reference node title "Some reference ' . $i .'" in the IEF table.');
    }
    // Check if all remaining nodes from all bundles are referenced.
    $count = 2;
    foreach ($bundle_nodes as $id => $title) {
      $cell = $this->xpath('//table[@id="ief-entity-table-edit-all-bundles-entities"]/tbody/tr[' . $count . ']/td[@class="inline-entity-form-node-label"]');
      $this->assertTrue($cell[0] == $title, 'Found reference node title "' . $title . '" in the IEF table.');
      $count++;
    }
  }

  /**
   * Tests if a referenced content can be edited while the referenced content is
   * newer than the referencing parent node.
   */
  public function testEditedInlineEntityValidation() {
    $this->setAllowExisting(TRUE);

    // Create referenced content.
    $referenced_nodes = $this->createReferenceContent(1);

    // Create first referencing node.
    $this->drupalCreateNode([
      'type' => 'ief_test_complex',
      'title' => 'First referencing node',
      'multi' => array_values($referenced_nodes),
    ]);
    $first_node = $this->drupalGetNodeByTitle('First referencing node');

    // Create second referencing node.
    $this->drupalCreateNode([
      'type' => 'ief_test_complex',
      'title' => 'Second referencing node',
      'multi' => array_values($referenced_nodes),
    ]);
    $second_node = $this->drupalGetNodeByTitle('Second referencing node');

    // Edit referenced content in first node.
    $this->drupalGet('node/' . $first_node->id() . '/edit');

    // Edit referenced node.
    $this->drupalPostAjaxForm(NULL, [], $this->getButtonName('//input[@type="submit" and @value="Edit" and @data-drupal-selector="edit-multi-entities-0-actions-ief-entity-edit"]'));
    $edit = [
      'multi[form][inline_entity_form][entities][0][form][title][0][value]' => 'Some reference updated',
    ];
    $this->drupalPostAjaxForm(NULL, $edit, $this->getButtonName('//input[@type="submit" and @value="Update node" and @data-drupal-selector="edit-multi-form-inline-entity-form-entities-0-form-actions-ief-edit-save"]'));

    // Save the first node after editing the reference.
    $edit = ['title[0][value]' => 'First node updated'];
    $this->drupalPostForm(NULL, $edit, t('Save'));

    // The changed value of the referenced content is now newer than the
    // changed value of the second node.

    // Edit referenced content in second node.
    $this->drupalGet('node/' . $second_node->id() . '/edit');

    // Edit referenced node.
    $this->drupalPostAjaxForm(NULL, [], $this->getButtonName('//input[@type="submit" and @value="Edit" and @data-drupal-selector="edit-multi-entities-0-actions-ief-entity-edit"]'));
    $edit = [
      'multi[form][inline_entity_form][entities][0][form][title][0][value]' => 'Some reference updated the second time',
    ];
    $this->drupalPostAjaxForm(NULL, $edit, $this->getButtonName('//input[@type="submit" and @value="Update node" and @data-drupal-selector="edit-multi-form-inline-entity-form-entities-0-form-actions-ief-edit-save"]'));

    // Save the second node after editing the reference.
    $edit = ['title[0][value]' => 'Second node updated'];
    $this->drupalPostForm(NULL, $edit, t('Save'));

    // Check if the referenced content could be edited.
    $this->assertNoText('The content has either been modified by another user, or you have already submitted modifications. As a result, your changes cannot be saved.', 'The referenced content could be edited.');
  }

  /**
   * Creates ief_reference_type nodes which shall serve as reference nodes.
   *
   * @param int $numNodes
   *   The number of nodes to create
   * @return array
   *   Array of created node ids keyed by labels.
   */
  protected function createReferenceContent($numNodes = 3) {
    $retval = [];
    for ($i = 1; $i <= $numNodes; $i++) {
      $this->drupalCreateNode([
        'type' => 'ief_reference_type',
        'title' => 'Some reference ' . $i,
        'first_name' => 'First Name ' . $i,
        'last_name' => 'Last Name ' . $i,
      ]);
      $node = $this->drupalGetNodeByTitle('Some reference ' . $i);
      $this->assertTrue($node, 'Created ief_reference_type node "' . $node->label() . '"');
      $retval[$node->label()] = $node->id();
    }
    return $retval;
  }

  /**
   * Sets allow_existing IEF setting.
   *
   * @param bool $flag
   *   "allow_existing" flag to be set.
   */
  protected function setAllowExisting($flag) {
    /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $display */
    $display = $this->entityFormDisplayStorage->load('node.ief_test_complex.default');
    $component = $display->getComponent('multi');
    $component['settings']['allow_existing'] = $flag;
    $display->setComponent('multi', $component)->save();
  }

  /**
   * Gets IEF button name.
   *
   * @param array $xpath
   *   Xpath of the button.
   *
   * @return string
   *   The name of the button.
   */
  protected function getButtonName($xpath) {
    $retval = '';
    /** @var \SimpleXMLElement[] $elements */
    if ($elements = $this->xpath($xpath)) {
      foreach ($elements[0]->attributes() as $name => $value) {
        if ($name == 'name') {
          $retval = $value;
          break;
        }
      }
    }
    return $retval;
  }

  /**
   * Creates a node for every node bundle.
   *
   * @return array
   *   Array of node titles keyed by ids.
   */
  protected function createNodeForEveryBundle() {
    $retval = [];
    $bundles = $this->container->get('entity.manager')->getBundleInfo('node');
    foreach ($bundles as $id => $value) {
      $this->drupalCreateNode(['type' => $id, 'title' => $value['label']]);
      $node = $this->drupalGetNodeByTitle($value['label']);
      $this->assertTrue($node, 'Created node "' . $node->label() . '"');
      $retval[$node->id()] = $value['label'];
    }
    return $retval;
  }

}
