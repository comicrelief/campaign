<?php

namespace Drupal\ds\Tests;

/**
 * Tests DS field plugins.
 *
 * @group ds
 */
class FieldPluginTest extends FastTestBase {

  /**
   * Test basic Display Suite fields plugins.
   */
  public function testFieldPlugin() {

    $this->dsSelectLayout();

    // Find the two field plugins from the test module on the node type.
    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->assertText('Test field plugin', 'Test field found on node.');
    // One is altered by hook_ds_fields_info_alter()
    $this->assertText('Field altered', 'Test field altered found on node.');

    $empty = array();
    $edit = array('layout' => 'ds_2col_stacked');
    $this->dsSelectLayout($edit, $empty, 'admin/config/people/accounts/display');

    // Fields can not be found on user.
    $this->drupalGet('admin/config/people/accounts/display');
    $this->assertNoText('Test code field from plugin', 'Test field not found on user.');
    $this->assertNoText('Field altered', 'Test field altered not found on user.');

    // Select layout.
    $this->dsSelectLayout();

    $fields = array(
      'fields[node_author][region]' => 'left',
      'fields[node_links][region]' => 'left',
      'fields[body][region]' => 'right',
      'fields[test_field][region]' => 'right',
      'fields[test_multiple_field][region]' => 'right',
      'fields[test_field_empty_string][region]' => 'right',
      'fields[test_field_empty_string][label]' => 'inline',
      'fields[test_field_false][region]' => 'right',
      'fields[test_field_false][label]' => 'inline',
      'fields[test_field_null][region]' => 'right',
      'fields[test_field_null][label]' => 'inline',
      'fields[test_field_nothing][region]' => 'right',
      'fields[test_field_nothing][label]' => 'inline',
      'fields[test_field_zero_int][region]' => 'right',
      'fields[test_field_zero_int][label]' => 'inline',
      'fields[test_field_zero_string][region]' => 'right',
      'fields[test_field_zero_string][label]' => 'inline',
      'fields[test_field_zero_float][region]' => 'right',
      'fields[test_field_zero_float][label]' => 'inline',
    );

    $this->dsSelectLayout();
    $this->dsConfigureUi($fields);

    // Create a node.
    $settings = array('type' => 'article');
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());

    $this->assertRaw('group-left', 'Template found (region left)');
    $this->assertRaw('group-right', 'Template found (region right)');
    $this->assertText('Test field plugin on node ' . $node->id(), 'Test field plugin found');
    $this->assertText('Test row one of multiple field plugin on node ' . $node->id(), 'First item of multiple field plugin found');
    $this->assertText('Test row two of multiple field plugin on node ' . $node->id(), 'Second item of multiple field plugin found');
    $this->assertText('Test field plugin that returns an empty string', 'Test field plugin that returns an empty string is visible.');
    $this->assertNoText('Test field plugin that returns FALSE', 'Test field plugin that returns FALSE is not visible.');
    $this->assertNoText('Test field plugin that returns NULL', 'Test field plugin that returns NULL is not visible.');
    $this->assertNoText('Test field plugin that returns nothing', 'Test field plugin that returns nothing is not visible.');
    $this->assertNoText('Test field plugin that returns an empty array', 'Test field plugin that returns an empty array is not visible.');
    $this->assertText('Test field plugin that returns zero as an integer', 'Test field plugin that returns zero as an integer is visible.');
    $this->assertText('Test field plugin that returns zero as a string', 'Test field plugin that returns zero as a string is visible.');
    $this->assertText('Test field plugin that returns zero as a floating point number', 'Test field plugin that returns zero as a floating point number is visible.');
  }

}
