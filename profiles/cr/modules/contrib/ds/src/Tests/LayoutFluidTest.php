<?php

/**
 * @file
 * Definition of Drupal\ds\Tests\LayoutFluidTest.
 */

namespace Drupal\ds\Tests;

/**
 * Tests DS layout plugins
 *
 * @group ds
 */
class LayoutFluidTest extends FastTestBase {

  /**
   * Test fluid Display Suite layouts.
   */
  function testFluidLayout() {
    // Assert our 2 tests layouts are found.
    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->assertRaw('Test Fluid two column', 'Test Fluid two column layout found');

    $layout = array(
      'layout' => 'dstest_2col_fluid',
    );

    $assert = array(
      'regions' => array(
        'left' => '<td colspan="8">' . t('Left') . '</td>',
        'right' => '<td colspan="8">' . t('Right') . '</td>',
      ),
    );

    $fields = array(
      'fields[node_author][region]' => 'left',
      'fields[node_links][region]' => 'left',
      'fields[body][region]' => 'left',
    );

    $this->dsSelectLayout($layout, $assert);
    $this->dsConfigureUI($fields);

    // Create a node.
    $settings = array('type' => 'article');
    $node = $this->drupalCreateNode($settings);

    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('group-left', 'Template found (region left)');
    $this->assertNoRaw('group-right', 'Empty region right hidden');
    $this->assertRaw('group-one-column', 'Group one column class set');
    $this->assertRaw('dstest-2col-fluid.css', 'Css file included');

    // Add fields to the right column
    $fields = array(
      'fields[node_author][region]' => 'left',
      'fields[node_links][region]' => 'left',
      'fields[body][region]' => 'right',
    );

    $this->dsSelectLayout($layout, $assert);
    $this->dsConfigureUI($fields);

    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('group-left', 'Template found (region left)');
    $this->assertRaw('group-right', 'Template found (region right)');
    $this->assertNoRaw('group-one-column', 'Group one column class not set');

    // Move all fields to the right column
    $fields = array(
      'fields[node_author][region]' => 'right',
      'fields[node_links][region]' => 'right',
      'fields[heavy_field][region]' => 'right',
      'fields[body][region]' => 'right',
    );

    $this->dsSelectLayout($layout, $assert);
    $this->dsConfigureUI($fields);

    $this->drupalGet('node/' . $node->id());
    $this->assertNoRaw('group-left', 'Empty region left hidden');
    $this->assertRaw('group-right', 'Template found (region right)');
    $this->assertRaw('group-one-column', 'Group one column class set');

  }

}
