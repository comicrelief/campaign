<?php

/*
 * @file
 * Contains \Drupal\block_visibility_groups\Tests\BlockVisibilityGroupsTestBase
 */

namespace Drupal\block_visibility_groups\Tests;

use Drupal\simpletest\WebTestBase;

/**
 *
 */
abstract class BlockVisibilityGroupsTestBase extends WebTestBase {

  /**
   * Modules to enable.
   *
   * Var array.
   */
  public static $modules = ['block', 'block_visibility_groups'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    // Create and login with user who can administer blocks.
    $this->drupalLogin($this->drupalCreateUser([
      'administer blocks',
    ]));
  }

  /**
   * @param $plugin_id
   * @param $group_id
   * @param array $settings
   *
   * @return \Drupal\block\Entity\Block
   */
  protected function placeBlockInGroup($plugin_id, $group_id, $settings = []) {
    $settings['label_display'] = 'visible';
    $settings['label'] = $this->randomMachineName();
    $settings['visibility']['condition_group']['block_visibility_group'] = $group_id;
    $block = $this->drupalPlaceBlock($plugin_id, $settings);
    return $block;
  }

  /**
   *
   */
  protected function placeBlockInGroupUI($plugin_id, $group_id, $title) {

    // Enable a standard block.
    $default_theme = $this->config('system.theme')->get('default');
    $edit = array(
      'id' => strtolower($this->randomMachineName(8)),
      'region' => 'sidebar_first',
      'settings[label]' => $title,
      'settings[label_display]' => 1,
    );
    $block_id = $edit['id'];
    if ($group_id) {
      $edit['visibility[condition_group][block_visibility_group]'] = $group_id;
    }

    $this->drupalGet('admin/structure/block/add/' . $plugin_id . '/' . $default_theme);

    $this->drupalPostForm(NULL, $edit, t('Save block'));
    $this->assertText('The block configuration has been saved.', 'Block was saved');

    // Just for Debug message.
    $this->drupalGet('admin/structure/block/manage/' . $edit['id']);
    $this->drupalGet('admin/structure/block/block-visibility-group/' . $group_id);
  }

}
