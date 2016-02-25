<?php

/**
 * @file
 * Contains \Drupal\inline_entity_form\Tests\InlineEntityFormWebTest.
 */

namespace Drupal\inline_entity_form\Tests;

use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Tests the IEF simple widget.
 *
 * @group inline_entity_form
 */
class InlineEntityFormSimpleWebTest extends InlineEntityFormTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['inline_entity_form_test'];

  /**
   * User with permissions to create content.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $user;

  /**
   * Field config storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorage
   */
  protected $fieldStorageConfigStorage;

  /**
   * Prepares environment for
   */
  protected function setUp() {
    parent::setUp();

    $this->user = $this->createUser([
      'create ief_simple_single content',
      'edit any ief_simple_single content',
      'edit any ief_test_custom content',
      'view own unpublished content',
    ]);

    $this->fieldStorageConfigStorage = $this->container
      ->get('entity_type.manager')
      ->getStorage('field_storage_config');
  }

  /**
   * Tests simple IEF widget with different cardinality options.
   *
   * @throws \Exception
   */
  protected function testSimpleCardinalityOptions() {
    $this->drupalLogin($this->user);
    $cardinality_options = [
      1 => 1,
      2 => 2,
      FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED => 3,
    ];
    /** @var \Drupal\field\FieldStorageConfigInterface $field_storage */
    $field_storage = $this->fieldStorageConfigStorage->load('node.single');
    foreach ($cardinality_options as $cardinality => $limit) {
      $field_storage->setCardinality($cardinality);
      $field_storage->save();

      $this->drupalGet('node/add/ief_simple_single');

      $this->assertText('Single node', 'Inline entity field widget title found.');
      $this->assertText('Reference a single node.', 'Inline entity field description found.');

      $add_more_xpath = '//input[@data-drupal-selector="edit-single-add-more"]';
      if ($cardinality == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED) {
        $this->assertFieldByXPath($add_more_xpath, NULL, 'Add more button exists');
      }
      else {
        $this->assertNoFieldByXPath($add_more_xpath, NULL, 'Add more button does NOT exist');
      }

      $edit = ['title[0][value]' => 'Host node'];
      for ($item_number = 0; $item_number < $limit; $item_number++) {
        $edit["single[$item_number][inline_entity_form][title][0][value]"] = 'Child node nr.' . $item_number;
        if ($cardinality == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED) {
          $next_item_number = $item_number + 1;
          $this->assertNoFieldByName("single[$next_item_number][inline_entity_form][title][0][value]", NULL, "Item $next_item_number does not appear before 'Add More' clicked");
          if ($item_number < $limit - 1) {
            $this->drupalPostAjaxForm(NULL, $edit, 'single_add_more');
            // This needed because the first time "add another item" is clicked it does not work
            // see https://www.drupal.org/node/2664626
            if ($item_number == 0) {
              $this->drupalPostAjaxForm(NULL, $edit, 'single_add_more');
            }

            $this->assertFieldByName("single[$next_item_number][inline_entity_form][title][0][value]", NULL, "Item $next_item_number does  appear after 'Add More' clicked");
          }

        }
      }
      $this->drupalPostForm(NULL, $edit, t('Save'));

      for ($item_number = 0; $item_number < $limit; $item_number++) {
        $this->assertText('Child node nr.' . $item_number, 'Label of referenced entity found.');
      }
    }
  }

  /**
   * Test Validation on Simple Widget.
   *
   * @throws \Exception
   */
  protected function testSimpleValidation() {
    $this->drupalLogin($this->user);
    $host_node_title = 'Host Validation Node';
    $this->drupalGet('node/add/ief_simple_single');

    $this->assertText('Single node', 'Inline entity field widget title found.');
    $this->assertText('Reference a single node.', 'Inline entity field description found.');
    $this->assertText('Positive int', 'Positive int field found.');

    $edit = ['title[0][value]' => $host_node_title];
    $this->drupalPostForm(NULL, $edit, t('Save'));

    $this->assertText('Title field is required.', 'Title validation fires on Inline Entity Form widget.');
    $this->assertUrl('node/add/ief_simple_single', [], 'On add page after validation error.');

    $child_title = 'Child node ' . $this->randomString();
    $edit['single[0][inline_entity_form][title][0][value]'] = $child_title;
    $edit['single[0][inline_entity_form][positive_int][0][value]'] = -1;
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertNoText('Title field is required.', 'Title validation passes on Inline Entity Form widget.');
    $this->assertText('Positive int must be higher than or equal to 1', 'Field validation fires on Inline Entity Form widget.');
    $this->assertUrl('node/add/ief_simple_single', [], 'On add page after validation error.');

    $edit['single[0][inline_entity_form][positive_int][0][value]'] = 1;
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertNoText('Title field is required.', 'Title validation passes on Inline Entity Form widget.');
    $this->assertNoText('Positive int must be higher than or equal to 1', 'Field validation fires on Inline Entity Form widget.');

    // Check that nodes were created correctly.
    $host_node = $this->getNodeByTitle($host_node_title);
    if ($this->assertNotNull($host_node, 'Host node created.')) {
      $this->assertUrl('node/' . $host_node->id(), [], 'On node view page after node add.');
      $child_node = $this->getNodeByTitle($child_title);
      if ($this->assertNotNull($child_node)) {
        $this->assertEqual($host_node->single[0]->target_id, $child_node->id(), 'Child node is referenced');
        $this->assertEqual($child_node->positive_int[0]->value,1, 'Child node int field correct.');
        $this->assertEqual($child_node->bundle(),'ief_test_custom', 'Child node is correct bundle.');
      }
    }
  }

}
