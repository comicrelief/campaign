<?php

/**
 * @file
 * Contains \Drupal\pathauto\Tests\PathautoMassDeleteTest.
 */

namespace Drupal\pathauto\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Mass delete functionality tests.
 *
 * @group pathauto
 */
class PathautoMassDeleteTest extends WebTestBase {

  use PathautoTestHelperTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node', 'taxonomy', 'pathauto');

  /**
   * Admin user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * The test nodes.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $nodes;

  /**
   * The test accounts.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $accounts;

  /**
   * The test terms.
   *
   * @var \Drupal\taxonomy\TermInterface
   */
  protected $terms;


  /**
   * {inheritdoc}
   */
  function setUp() {
    parent::setUp();

    $permissions = array(
      'administer pathauto',
      'administer url aliases',
      'create url aliases',
    );
    $this->adminUser = $this->drupalCreateUser($permissions);
    $this->drupalLogin($this->adminUser);

    $this->createPattern('node', '/content/[node:title]');
    $this->createPattern('user', '/users/[user:name]');
    $this->createPattern('taxonomy_term', '/[term:vocabulary]/[term:name]');
  }

  /**
   * Tests the deletion of all the aliases.
   */
  function testDeleteAll() {
    // 1. Test that deleting all the aliases, of any type, works.
    $this->generateAliases();
    $edit = array(
      'delete[all_aliases]' => TRUE,
    );
    $this->drupalPostForm('admin/config/search/path/delete_bulk', $edit, t('Delete aliases now!'));
    $this->assertText(t('All of your path aliases have been deleted.'));
    $this->assertUrl('admin/config/search/path/delete_bulk');

    // Make sure that all of them are actually deleted.
    $aliases = db_select('url_alias', 'ua')->fields('ua', array())->execute()->fetchAll();
    $this->assertEqual($aliases, array(), "All the aliases have been deleted.");

    // 2. Test deleting only specific (entity type) aliases.
    $manager = $this->container->get('plugin.manager.alias_type');
    $pathauto_plugins = array('canonical_entities:node' => 'nodes', 'canonical_entities:taxonomy_term' => 'terms', 'canonical_entities:user' => 'accounts');
    foreach ($pathauto_plugins as $pathauto_plugin => $attribute) {
      $this->generateAliases();
      $edit = array(
        'delete[plugins][' . $pathauto_plugin . ']' => TRUE,
      );
      $this->drupalPostForm('admin/config/search/path/delete_bulk', $edit, t('Delete aliases now!'));
      $alias_type = $manager->createInstance($pathauto_plugin);
      $this->assertRaw(t('All of your %label path aliases have been deleted.', array('%label' => $alias_type->getLabel())));
      // Check that the aliases were actually deleted.
      foreach ($this->{$attribute} as $entity) {
        $this->assertNoEntityAlias($entity);
      }

      // Check that the other aliases are not deleted.
      foreach ($pathauto_plugins as $_pathauto_plugin => $_attribute) {
        // Skip the aliases that should be deleted.
        if ($_pathauto_plugin == $pathauto_plugin) {
          continue;
        }
        foreach ($this->{$_attribute} as $entity) {
          $this->assertEntityAliasExists($entity);
        }
      }
    }
  }

  /**
   * Helper function to generate aliases.
   */
  function generateAliases() {
    // We generate a bunch of aliases for nodes, users and taxonomy terms. If
    // the entities are already created we just update them, otherwise we create
    // them.
    if (empty($this->nodes)) {
      for ($i = 1; $i <= 5; $i++) {
        $node = $this->drupalCreateNode();
        $this->nodes[$node->id()] = $node;
      }
    }
    else {
      foreach ($this->nodes as $node) {
        $node->save();
      }
    }

    if (empty($this->accounts)) {
      for ($i = 1; $i <= 5; $i++) {
        $account = $this->drupalCreateUser();
        $this->accounts[$account->id()] = $account;
      }
    }
    else {
      foreach ($this->accounts as $id => $account) {
        $account->save();
      }
    }

    if (empty($this->terms)) {
      $vocabulary = $this->addVocabulary(array('name' => 'test vocabulary', 'vid' => 'test_vocabulary'));
      for ($i = 1; $i <= 5; $i++) {
        $term = $this->addTerm($vocabulary);
        $this->terms[$term->id()] = $term;
      }
    }
    else {
      foreach ($this->terms as $term) {
        $term->save();
      }
    }

    // Check that we have aliases for the entities.
    foreach (array('nodes', 'accounts', 'terms') as $attribute) {
      foreach ($this->{$attribute} as $entity) {
        $this->assertEntityAliasExists($entity);
      }
    }
  }

}
