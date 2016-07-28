<?php

/**
 * @file
 * Contains \Drupal\ds\Tests\DynamicFieldPluginTest.
 */

namespace Drupal\ds\Tests;

/**
 * Tests for managing custom code, and block fields.
 *
 * @group ds
 */
class DynamicFieldPluginTest extends FastTestBase {

  /**
   * Test Display fields.
   */
  function testDSFields() {

    $edit = array(
      'name' => 'Test field',
      'id' => 'test_field',
      'entities[node]' => '1',
      'content[value]' => 'Test field',
    );

    $this->dsCreateTokenField($edit);

    // Create the same and assert it already exists.
    $this->drupalPostForm('admin/structure/ds/fields/manage_token', $edit, t('Save'));
    $this->assertText(t('The machine-readable name is already in use. It must be unique.'), t('Field testing already exists.'));

    $this->dsSelectLayout();

    // Assert it's found on the Field UI for article.
    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->assertRaw('fields[dynamic_token_field:node-test_field][weight]', t('Test field found on node article.'));

    // Assert it's not found on the Field UI for users.
    $this->drupalGet('admin/config/people/accounts/display');
    $this->assertNoRaw('fields[dynamic_token_field:node-test_field][weight]', t('Test field not found on user.'));

    // Update testing label
    $edit = array(
      'name' => 'Test field 2',
    );
    $this->drupalPostForm('admin/structure/ds/fields/manage_token/test_field', $edit, t('Save'));
    $this->assertText(t('The field Test field 2 has been saved'), t('Test field label updated'));

    // Use the Field UI limit option.
    $this->dsSelectLayout(array(), array(), 'admin/structure/types/manage/page/display');
    $this->dsSelectLayout(array(), array(), 'admin/structure/types/manage/article/display/teaser');
    $edit = array(
      'ui_limit' => 'article|default',
    );
    $this->drupalPostForm('admin/structure/ds/fields/manage_token/test_field', $edit, t('Save'));

    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->assertRaw('fields[dynamic_token_field:node-test_field][weight]', t('Test field field found on node article, default.'));

    $this->drupalGet('admin/structure/types/manage/article/display/teaser');
    $this->assertNoRaw('fields[dynamic_token_field:node-test_field][weight]', t('Test field field not found on node article, teaser.'));
    $this->drupalGet('admin/structure/types/manage/page/display');
    $this->assertNoRaw('fields[dynamic_token_field:node-test_field][weight]', t('Test field field not found on node page, default.'));
    $edit = array(
      'ui_limit' => 'article|*',
    );
    $this->drupalPostForm('admin/structure/ds/fields/manage_token/test_field', $edit, t('Save'));
    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->assertRaw('fields[dynamic_token_field:node-test_field][weight]', t('Test field field found on node article, default.'));
    $this->drupalGet('admin/structure/types/manage/article/display/teaser');
    $this->assertRaw('fields[dynamic_token_field:node-test_field][weight]', t('Test field field found on node article, teaser.'));

    // Remove the field.
    $this->drupalPostForm('admin/structure/ds/fields/delete/test_field', array(), t('Confirm'));
    $this->assertText(t('The field Test field 2 has been deleted'), t('Test field removed'));

    // Assert the field is gone at the manage display screen.
    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->assertNoRaw('fields[dynamic_token_field:node-test_field][weight]', t('Test field field not found on node article.'));

    // Block fields.
    $edit = array(
      'name' => 'Test block field',
      'id' => 'test_block_field',
      'entities[node]' => '1',
      'block' => 'system_powered_by_block',
    );

    $this->dsCreateBlockField($edit);

    // Create the same and assert it already exists.
    $this->drupalPostForm('admin/structure/ds/fields/manage_block', $edit, t('Save'));
    $this->assertText(t('The machine-readable name is already in use. It must be unique.'), t('Block test field already exists.'));

    $this->dsSelectLayout();

    // Assert it's found on the Field UI for article.
    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->assertRaw('fields[dynamic_block_field:node-test_block_field][weight]', t('Test block field found on node article.'));

    // Assert it's not found on the Field UI for users.
    $this->drupalGet('admin/config/people/accounts/display');
    $this->assertNoRaw('fields[dynamic_block_field:node-test_block_field][weight]', t('Test block field not found on user.'));

    // Update testing label
    $edit = array(
      'name' => 'Test block field 2',
    );
    $this->drupalPostForm('admin/structure/ds/fields/manage_block/test_block_field', $edit, t('Save'));
    $this->assertText(t('The field Test block field 2 has been saved'), t('Test field label updated'));

    // Remove the block field.
    $this->drupalPostForm('admin/structure/ds/fields/delete/test_block_field', array(), t('Confirm'));
    $this->assertText(t('The field Test block field 2 has been deleted'), t('Test field removed'));

    // Assert the block field is gone at the manage display screen.
    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->assertNoRaw('fields[dynamic_block_field:node-test_block_field][weight]', t('Test block field not found on node article.'));

    // Create a configurable block field
    $edit = array(
      'name' => 'Configurable block',
      'id' => 'test_block_configurable',
      'entities[node]' => '1',
      'block' => 'system_menu_block:tools',
    );

    $this->dsCreateBlockField($edit);

    // Try to set the depth to 3, to ensure we can save the block
    $edit = array(
      'depth' => '3',
    );
    $this->drupalPostForm('admin/structure/ds/fields/manage_block/test_block_configurable/block_config', $edit, t('Save'));

    // Assert it's found on the Field UI for article.
    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->assertRaw('fields[dynamic_block_field:node-test_block_configurable][weight]', t('Test configurable block field found on node article.'));

    // Assert it's not found on the Field UI for users.
    $this->drupalGet('admin/config/people/accounts/display');
    $this->assertNoRaw('fields[dynamic_block_field:node-test_block_configurable][weight]', t('Test configurable block field not found on user.'));

    // Add block to display
    $fields = array(
      'fields[dynamic_block_field:node-test_block_configurable][region]' => 'left',
    );
    $this->dsConfigureUI($fields, 'admin/structure/types/manage/article/display');

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->entitiesTestSetup();

    // Look at node and verify the menu is visible
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('Add content', t('Tools menu found.'));

    // Try to set the depth to 3, to ensure we can save the block
    $edit = array(
      'level' => '2',
    );
    $this->drupalPostForm('admin/structure/ds/fields/manage_block/test_block_configurable/block_config', $edit, t('Save'));

    // Look at node and verify the menu is not visible
    $this->drupalGet('node/' . $node->id());
    $this->assertNoRaw('Add content', t('Tools menu not found.'));
  }
}
