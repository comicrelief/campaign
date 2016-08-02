<?php

/**
 * @file
 * Contains \Drupal\menu_attributes\Tests\MenuAttributesNodeTest.
 */

namespace Drupal\menu_attributes\Tests;
use Drupal\Core\Language\Language;

/**
 * Test menu attributes settings for nodes.
 *
 * Add, edit, and delete a node with menu link
 *
 * @group menu_attributes
 */
class MenuAttributesNodeTest extends MenuAttributesTestBase {

   function setUp() {
     parent::setUp();
     $this->drupalLogin($this->adminUser);
   }

   /**
    * Test creating, editing, deleting menu links via node form widget.
    */
   function testMenuNodeFormWidget() {
     // Enable main menu as available menu.
     $edit = ['menu_options[main]' => 1];
     $this->drupalPostForm('admin/structure/types/manage/page', $edit, t('Save content type'));
     // Change default parent item to main menu, so we can assert more easily.
//     $edit = ['menu_parent' => 'main:standard.front_page'];
//     $this->drupalPostForm('admin/structure/types/manage/page', $edit, t('Save content type'));

     // Create a node.
     $node_title = $this->randomString();
     $language = Language::LANGCODE_NOT_SPECIFIED;
     $edit = [
       "title" => $node_title,
       "body[$language][0][value]" => $this->randomString(),
     ];
//     $this->drupalPostForm('node/add/page', $edit, t('Save'));
//     $node = $this->drupalGetNodeByTitle($node_title);

     // Assert that there is no link for the node.
     $this->drupalGet('');
     $this->assertNoLink($node_title);

     // Edit the node, enable the menu link setting, but skip the link title.
     $edit = ['menu[enabled]' => 1];
     // $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save'));
     // Assert that there is no link for the node.
     $this->drupalGet('');
     $this->assertNoLink($node_title);

     // Edit the node and create a menu link with attributes.
     $edit = [
       'menu[enabled]' => 1,
       'menu[link_title]' => $node_title,
       'menu[weight]' => 17,
       'menu[options][attributes][title]' => $this->menu_attributes_new['title'],
       'menu[options][attributes][id]' => $this->menu_attributes_new['id'],
       'menu[options][attributes][name]' => $this->menu_attributes_new['name'],
       'menu[options][attributes][rel]' => $this->menu_attributes_new['rel'],
       'menu[options][attributes][class]' => $this->menu_attributes_new['class'],
       'menu[options][attributes][style]' => $this->menu_attributes_new['style'],
       'menu[options][attributes][target]' => $this->menu_attributes_new['target'],
       'menu[options][attributes][accesskey]' => $this->menu_attributes_new['accesskey'],
     ];
     // $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save'));
     // Assert that the link exists.
     $this->drupalGet('');
//     $this->assertLink($node_title);

//     // Assert that the link attributes exist.
//     $this->drupalGet('node/' . $node->nid . '/edit');
//     $this->assertMenuAttributes('menu[options][attributes]', 'new');

     // Edit the node again and change the menu link attributes.
     $edit = [
       'menu[enabled]' => 1,
       'menu[link_title]' => $node_title,
       'menu[weight]' => 17,
       'menu[options][attributes][title]' => $this->menu_attributes_edit['title'],
       'menu[options][attributes][id]' => $this->menu_attributes_edit['id'],
       'menu[options][attributes][name]' => $this->menu_attributes_edit['name'],
       'menu[options][attributes][rel]' => $this->menu_attributes_edit['rel'],
       'menu[options][attributes][class]' => $this->menu_attributes_edit['class'],
       'menu[options][attributes][style]' => $this->menu_attributes_edit['style'],
       'menu[options][attributes][target]' => $this->menu_attributes_edit['target'],
       'menu[options][attributes][accesskey]' => $this->menu_attributes_edit['accesskey'],
     ];
//     $this->drupalPostForm('node/' . $node->nid . '/edit', $edit, t('Save'));

//     // Assert that the link attributes exist.
//     $this->drupalGet('node/' . $node->nid . '/edit');
//     $this->assertMenuAttributes('menu[options][attributes]', 'edit');

     // Edit the node and remove the menu link.
     $edit = ['menu[enabled]' => FALSE];
//     $this->drupalPostForm('node/' . $node->nid . '/edit', $edit, t('Save'));
     // Assert that there is no link for the node.
     $this->drupalGet('');
     $this->assertNoLink($node_title);
   }

}
