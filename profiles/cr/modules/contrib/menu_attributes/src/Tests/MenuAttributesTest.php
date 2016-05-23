<?php
/**
 * @file
 * Contains \Drupal\menu_attributes\Tests\MenuAttributesTest.
 */

namespace Drupal\menu_attributes\Tests;

/**
 * Test menu attributes basic functionality.
 *
 * @group menu_attributes
 */
class MenuAttributesTest extends MenuAttributesTestBase {

  /**
   * Tests menu attributes functionality.
   */
  function testMenuAttributes() {
    // Login the user.
    $this->drupalLogin($this->adminUser);

    $menu_name = 'main';

    // Add a node to be used as a link for menu links.
    $node = $this->drupalCreateNode(['type' => 'page']);

    // Add a menu link.
//    $item = $this->crudMenuLink(0, 0, 'node/' . $node->id(), $menu_name);

//    $this->drupalGet('admin/structure/menu/item/' . $item['mlid'] . '/edit');
//    $this->assertMenuAttributes('options[attributes]', 'new');

    // Edit the previously created menu link.
//    $item = $this->crudMenuLink($item['mlid'], 0, 'node/' . $node->id(), $menu_name);

//    $this->drupalGet('admin/structure/menu/item/' . $item['mlid'] . '/edit');
//    $this->assertMenuAttributes('options[attributes]', 'edit');
  }

}
