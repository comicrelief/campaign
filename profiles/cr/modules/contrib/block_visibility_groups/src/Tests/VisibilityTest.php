<?php

namespace Drupal\block_visibility_groups\Tests;

use Drupal\block_visibility_groups\Entity\BlockVisibilityGroup;

/**
 * Tests the block_visibility_groups Visibility Settings.
 *
 * @group block_visibility_groups
 */
class VisibilityTest extends BlockVisibilityGroupsTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create Basic page and Article node types.
    if ($this->profile != 'standard') {
      $this->drupalCreateContentType(array(
        'type' => 'page',
        'name' => 'Basic page',
        'display_submitted' => FALSE,
      ));
      $this->drupalCreateContentType(array(
        'type' => 'article',
        'name' => 'Article',
      ));
    }
  }

  /**
   * Modules to enable.
   *
   * Var array.
   */
  public static $modules = ['block', 'block_visibility_groups', 'node'];

  /**
   *
   */
  public function testSingleConditions() {
    // @todo Condition with node doesn't work for some reason.
    $config = [
      'id' => 'node_type',
      'bundles' => ['page'],
      'negate' => 0,
      'context_mapping' => ['node' => '@node.node_route_context:node'],
    ];
    $configs['request'] = [
      'id' => 'request_path',
      'pages' => '/node/*',
      'negate' => 0,
    ];
    $group = $this->createGroup($configs);

    $block_title = $this->randomMachineName();
    $this->placeBlockInGroupUI('system_powered_by_block', $group->id(), $block_title);

    $page_node = $this->drupalCreateNode();
    $this->drupalGet('node/' . $page_node->id());
    $this->assertText($block_title, 'Block shows up on page node when added via UI.');

    $this->drupalGet('user');
    $this->assertNoText($block_title, 'Block does not show up on user page when added via UI.');

    $block = $this->placeBlockInGroup('system_powered_by_block', $group->id());
    $this->drupalGet('node/' . $page_node->id());
    $this->assertText($block->label(), 'Block shows up on page node.');
    $this->drupalGet('user');
    $this->assertNoText($block->label(), 'Block does not show up on user page.');
  }

  /**
   * @param $config
   *
   * @return static
   */
  private function createGroup($configs) {
    $group = BlockVisibilityGroup::create(
      [
        'id' => $this->randomMachineName(),
        'label' => $this->randomString(),
      ]
    );

    $group->save();
    foreach ($configs as $config) {
      $group->addCondition($config);
    }
    $group->save();
    return $group;
  }

}
