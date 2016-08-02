<?php
/**
 * @file
 * Contains \Drupal\menu_attributes\Tests\MenuAttributesTestBase.
 */

namespace Drupal\menu_attributes\Tests;

use Drupal\menu_ui\Tests\MenuWebTestBase;

/**
 * Helper test class with some added functions for testing.
 */
abstract class MenuAttributesTestBase extends MenuWebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['menu_ui', 'menu_attributes', 'menu_link_content', 'block', 'node'];

  /**
   * A user with administration rights.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * An authenticated user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $authenticatedUser;

  protected $menu_attributes_new = [];
  protected $menu_attributes_edit = [];

  function setUp() {
    parent::setUp();

    $this->drupalPlaceBlock('page_title_block');

    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Page']);

    // Create users.
    $this->adminUser = $this->drupalCreateUser([
      'administer menu attributes',
      'access administration pages',
      'administer blocks',
      'administer menu',
      'administer content types',
    ]);
    $this->authenticatedUser = $this->drupalCreateUser([]);

     $this->menu_attributes_new = [
       'title' => $this->randomMachineName(10),
       'id' => $this->randomMachineName(10),
       'name' => $this->randomMachineName(10),
       'rel' => $this->randomMachineName(10),
       'class' => $this->randomMachineName(10),
       'style' => $this->randomMachineName(10),
       'target' => '_top',
       'accesskey' => $this->randomMachineName(1),
     ];

     $this->menu_attributes_edit = [
       'title' => $this->randomMachineName(10),
       'id' => $this->randomMachineName(10),
       'name' => $this->randomMachineName(10),
       'rel' => $this->randomMachineName(10),
       'class' => $this->randomMachineName(10),
       'style' => $this->randomMachineName(10),
       'target' => '_self',
       'accesskey' => $this->randomMachineName(1),
     ];
   }

  /**
   * Add or edit a menu link using the menu module UI.
   *
   * @param integer $plid
   *   Parent menu link id.
   * @param string $link
   *   Link path.
   * @param string $menu_name
   *   Menu name.
   *
   * @return array Menu link created.
   */
  function crudMenuLink($mlid = 0, $plid = 0, $link = '<front>', $menu_name = 'navigation') {
//    // View add/edit menu link page.
//    if (empty($mlid)) {
//      $this->drupalGet("admin/structure/menu/manage/$menu_name/add");
//      $menu_attributes = $this->menu_attributes_new;
//    }
//    else {
//      $this->drupalGet("admin/structure/menu/item/$mlid/edit");
//      $menu_attributes = $this->menu_attributes_edit;
//    }
//    $this->assertResponse(200);
//
//    $title = '!link_' . $this->randomMachineName(16);
//    $edit = [
//      'link_path' => $link,
//      'link_title' => $title,
//      'enabled' => TRUE, // Use this to disable the menu and test.
//      'expanded' => TRUE, // Setting this to true should test whether it works when we do the std_user tests.
//      'parent' =>  $menu_name . ':' . $plid,
//      'weight' => '0',
//      'options[attributes][title]' => $menu_attributes['title'],
//      'options[attributes][id]' => $menu_attributes['id'],
//      'options[attributes][name]' => $menu_attributes['name'],
//      'options[attributes][rel]' => $menu_attributes['rel'],
//      'options[attributes][class]' => $menu_attributes['class'],
//      'options[attributes][style]' => $menu_attributes['style'],
//      'options[attributes][target]' => $menu_attributes['target'],
//      'options[attributes][accesskey]' => $menu_attributes['accesskey'],
//    ];
//
//    // Add menu link.
//    $this->drupalPostForm(NULL, $edit, t('Save'));
//
//    $item = db_query('SELECT * FROM {menu_links} WHERE link_title = :title', array(':title' => $title))->fetchAssoc();
//
//    return $item;
  }

  /**
   * @param $form_parent
   * @param string $action
   */
  function assertMenuAttributes($form_parent, $action = 'new') {
    if ($action == 'new') {
      foreach ($this->menu_attributes_new as $attribute => $value) {
        $this->assertFieldByName($form_parent . '[' . $attribute . ']', $value, t("'$attribute' attribute correct in edit form."));
      }
    }
    else {
      foreach ($this->menu_attributes_edit as $attribute => $value) {
        $this->assertFieldByName($form_parent . '[' . $attribute . ']', $value, t("New '$attribute' attribute correct in edit form."));
      }
    }
  }

}
