<?php

namespace Drupal\pathauto\Tests;

use Drupal\pathauto\PathautoState;
use Drupal\simpletest\WebTestBase;

/**
 * Bulk update functionality tests.
 *
 * @group pathauto
 */
class PathautoBulkUpdateTest extends WebTestBase {

  use PathautoTestHelperTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node', 'pathauto');

  /**
   * Admin user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * The created nodes.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $nodes;

  /**
   * {inheritdoc}
   */
  function setUp() {
    parent::setUp();

    // Allow other modules to add additional permissions for the admin user.
    $permissions = array(
      'administer pathauto',
      'administer url aliases',
      'create url aliases',
    );
    $this->adminUser = $this->drupalCreateUser($permissions);
    $this->drupalLogin($this->adminUser);

    $this->createPattern('node', '/content/[node:title]');
    $this->createPattern('user', '/users/[user:name]');
  }

  function testBulkUpdate() {
    // Create some nodes.
    $this->nodes = array();
    for ($i = 1; $i <= 5; $i++) {
      $node = $this->drupalCreateNode();
      $this->nodes[$node->id()] = $node;
    }

    // Clear out all aliases.
    $this->deleteAllAliases();

    // Bulk create aliases.
    $edit = array(
      'update[canonical_entities:node]' => TRUE,
      'update[canonical_entities:user]' => TRUE,
    );
    $this->drupalPostForm('admin/config/search/path/update_bulk', $edit, t('Update'));

    // This has generated 7 aliases: 5 nodes and 2 users.
    $this->assertText('Generated 7 URL aliases.');

    // Check that aliases have actually been created.
    foreach ($this->nodes as $node) {
      $this->assertEntityAliasExists($node);
    }
    $this->assertEntityAliasExists($this->adminUser);

    // Add a new node.
    $new_node = $this->drupalCreateNode(array('path' => array('alias' => '', 'pathauto' => PathautoState::SKIP)));

    // Run the update again which should not run against any nodes.
    $this->drupalPostForm('admin/config/search/path/update_bulk', $edit, t('Update'));
    $this->assertText('No new URL aliases to generate.');

    $this->assertNoEntityAliasExists($new_node);
  }

  /**
   * Tests alias generation for nodes that existed before installing Pathauto.
   */
  function testBulkUpdateExistingContent() {
    // Create a node.
    $node = $this->drupalCreateNode();

    // Delete its alias and Pathauto metadata.
    \Drupal::service('pathauto.alias_storage_helper')->deleteEntityPathAll($node);
    $node->path->first()->get('pathauto')->purge();
    \Drupal::entityTypeManager()->getStorage('node')->resetCache(array($node->id()));

    // Execute bulk generation.
    // Bulk create aliases.
    $edit = array(
      'update[canonical_entities:node]' => TRUE,
    );
    $this->drupalPostForm('admin/config/search/path/update_bulk', $edit, t('Update'));

    // Verify that the alias was created for the node.
    $this->assertText('Generated 1 URL alias.');
  }

}
