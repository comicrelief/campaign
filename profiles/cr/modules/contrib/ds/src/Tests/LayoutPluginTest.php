<?php

/**
 * @file
 * Contains \Drupal\ds\Tests\LayoutPluginTest.
 */

namespace Drupal\ds\Tests;

/**
 * Tests DS layout plugins
 *
 * @group ds
 */
class LayoutPluginTest extends FastTestBase {

  /**
   * Test basic Display Suite layout plugins.
   */
  function testFieldPlugin() {
    // Assert our 2 tests layouts are found.
    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->assertRaw('Test One column', 'Test One column layout found');
    $this->assertRaw('Test Two column', 'Test Two column layout found');

    $layout = array(
      'layout' => 'dstest_2col',
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
      'fields[body][region]' => 'right',
    );

    $this->dsSelectLayout($layout, $assert);
    $this->dsConfigureUI($fields);

    // Create a node.
    $settings = array('type' => 'article');
    $node = $this->drupalCreateNode($settings);

    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('group-left', 'Template found (region left)');
    $this->assertRaw('group-right', 'Template found (region right)');
    $this->assertRaw('dstest-2col.css', 'Css file included');

    // Alter a region
    $settings = array(
      'type' => 'article',
      'title' => 'Alter me!',
    );
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('cool!', 'Region altered');
  }

  /**
   * Test reset layout
   */
  function testResetLayout() {
    $layout = array(
      'layout' => 'ds_reset',
    );

    $assert = array(
      'regions' => array(
        'ds_content' => '<td colspan="8">' . t('Content') . '</td>',
      ),
    );

    $fields = array(
      'fields[node_author][region]' => 'ds_content',
    );

    $this->dsSelectLayout($layout, $assert);
    $this->dsConfigureUI($fields);

    // Create a node.
    $settings = array('type' => 'article');
    $node = $this->drupalCreateNode($settings);

    $this->drupalGet('node/' . $node->id());
  }

}
