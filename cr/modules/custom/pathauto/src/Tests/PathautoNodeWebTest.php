<?php

/**
 * @file
 * Contains \Drupal\pathauto\Tests\PathautoNodeWebTest.
 */

namespace Drupal\pathauto\Tests;
use Drupal\simpletest\WebTestBase;

/**
 * Tests pathauto node UI integration.
 *
 * @group pathauto
 */
class PathautoNodeWebTest extends WebTestBase {

  use PathautoTestHelperTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node', 'pathauto', 'views');

  /**
   * Admin user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {inheritdoc}
   */
  function setUp() {
    parent::setUp();

    $this->drupalCreateContentType(array('type' => 'page', 'name' => 'Basic page'));
    $this->drupalCreateContentType(array('type' => 'article'));

    // Allow other modules to add additional permissions for the admin user.
    $permissions = array(
      'administer pathauto',
      'administer url aliases',
      'create url aliases',
      'administer nodes',
      'bypass node access',
      'access content overview',
    );
    $this->adminUser = $this->drupalCreateUser($permissions);
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Tests editing nodes with different settings.
   */
  function testNodeEditing() {
    // Ensure that the Pathauto checkbox is checked by default on the node add form.
    $this->drupalGet('node/add/page');
    $this->assertFieldChecked('edit-path-0-pathauto');

    // Create a node by saving the node form.
    $title = ' Testing: node title [';
    $automatic_alias = '/content/testing-node-title';
    $this->drupalPostForm(NULL, array('title[0][value]' => $title), t('Save and publish'));
    $node = $this->drupalGetNodeByTitle($title);

    // Look for alias generated in the form.
    $this->drupalGet("node/{$node->id()}/edit");
    $this->assertFieldChecked('edit-path-0-pathauto');
    $this->assertFieldByName('path[0][alias]', $automatic_alias, 'Generated alias visible in the path alias field.');

    // Check whether the alias actually works.
    $this->drupalGet($automatic_alias);
    $this->assertText($title, 'Node accessible through automatic alias.');

    // Manually set the node's alias.
    $manual_alias = '/content/' . $node->id();
    $edit = array(
      'path[0][pathauto]' => FALSE,
      'path[0][alias]' => $manual_alias,
    );
    $this->drupalPostForm($node->urlInfo('edit-form'), $edit, t('Save and keep published'));
    $this->assertRaw(t('@type %title has been updated.', array('@type' => 'page', '%title' => $title)));

    // Check that the automatic alias checkbox is now unchecked by default.
    $this->drupalGet("node/{$node->id()}/edit");
    $this->assertNoFieldChecked('edit-path-0-pathauto');
    $this->assertFieldByName('path[0][alias]', $manual_alias);

    // Submit the node form with the default values.
    $this->drupalPostForm(NULL, array('path[0][pathauto]' => FALSE), t('Save and keep published'));
    $this->assertRaw(t('@type %title has been updated.', array('@type' => 'page', '%title' => $title)));

    // Test that the old (automatic) alias has been deleted and only accessible
    // through the new (manual) alias.
    $this->drupalGet($automatic_alias);
    $this->assertResponse(404, 'Node not accessible through automatic alias.');
    $this->drupalGet($manual_alias);
    $this->assertText($title, 'Node accessible through manual alias.');

    // Test that the manual alias is not kept for new nodes when the pathauto
    // checkbox is ticked.
    $title = 'Automatic Title';
    $edit = array(
      'title[0][value]' => $title,
      'path[0][pathauto]' => TRUE,
      'path[0][alias]' => '/should-not-get-created',
    );
    $this->drupalPostForm('node/add/page', $edit, t('Save and publish'));
    $this->assertNoAliasExists(array('alias' => 'should-not-get-created'));
    $node = $this->drupalGetNodeByTitle($title);
    $this->assertEntityAlias($node, '/content/automatic-title');

    // Remove the pattern for nodes, the pathauto checkbox should not be
    // displayed.
    $config = $this->config('pathauto.pattern');
    $config->set('patterns.node.default', '');
    $config->save();
    \Drupal::service('pathauto.manager')->resetCaches();

    $this->drupalGet('node/add/article');
    $this->assertNoFieldById('edit-path-0-pathauto');
    $this->assertFieldByName('path[0][alias]', '');

    $edit = array();
    $edit['title'] = 'My test article';
    $this->drupalCreateNode($edit);
    //$this->drupalPostForm(NULL, $edit, t('Save and keep published'));
    $node = $this->drupalGetNodeByTitle($edit['title']);

    // Pathauto checkbox should still not exist.
    $this->drupalGet($node->urlInfo('edit-form'));
    $this->assertNoFieldById('edit-path-0-pathauto');
    $this->assertFieldByName('path[0][alias]', '');
    $this->assertNoEntityAlias($node);
  }

  /**
   * Test node operations.
   */
  function testNodeOperations() {
    $node1 = $this->drupalCreateNode(array('title' => 'node1'));
    $node2 = $this->drupalCreateNode(array('title' => 'node2'));

    // Delete all current URL aliases.
    $this->deleteAllAliases();

    $edit = array(
      'action' => 'pathauto_update_alias_node',
      // @todo - here we expect the $node1 to be at 0 position, any better way?
      'node_bulk_form[0]' => TRUE,
    );
    $this->drupalPostForm('admin/content', $edit, t('Apply'));
    $this->assertRaw(\Drupal::translation()->formatPlural(1, '%action was applied to @count item.', '%action was applied to @count items.', array(
      '%action' => 'Update URL-Alias',
    )));

    $this->assertEntityAlias($node1, '/content/' . $node1->getTitle());
    $this->assertEntityAlias($node2, '/node/' . $node2->id());
  }

}
