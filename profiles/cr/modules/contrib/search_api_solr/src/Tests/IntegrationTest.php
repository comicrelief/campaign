<?php

namespace Drupal\search_api_solr\Tests;

use Drupal\Component\Utility\Html;
use Drupal\facets\Tests\BlockTestTrait;
use Drupal\facets\Tests\ExampleContentTrait;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\SearchApiException;
use Drupal\search_api\Tests\WebTestBase;

/**
 * Tests the overall functionality of the Search API framework and admin UI.
 *
 * @group search_api_solr
 */
class IntegrationTest extends WebTestBase {

  use BlockTestTrait;
  use ExampleContentTrait;

  /**
   * The ID of the search server used for this test.
   *
   * @var string
   */
  protected $serverId;

  /**
   * The backend of the search server used for this test.
   *
   * @var string
   */
  protected $serverBackend = 'search_api_solr';

  /**
   * A storage instance for indexes.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $indexStorage;

  /**
   * {@inheritdoc}
   */
  public static $modules = array(
    'node',
    'search_api',
    'search_api_solr',
    'search_api_solr_test',
    'field_ui',
    'block',
    'facets',
    'views',
  );

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->indexStorage = \Drupal::entityTypeManager()->getStorage('search_api_index');

    $this->drupalLogin($this->adminUser);

    $filepath = drupal_get_path('module', 'search_api_solr') . '/vendor/autoload.php';
    if (!class_exists('Solarium\\Client') && ($filepath != DRUPAL_ROOT . '/core/vendor/autoload.php')) {
      require $filepath;
    }
  }

  /**
   * Tests various operations via the Search API's admin UI.
   */
  public function testFramework() {
    // Login as an admin user for the rest of the tests.
    $this->drupalLogin($this->adminUser);

    $this->createServer();
    $this->createIndex();
    $this->checkContentEntityTracking();
    $this->changeIndexServer();
  }

  /**
   * Tests basic facets integration.
   */
  public function testFacets() {
    // Create the users used for the tests.
    $admin_user = $this->drupalCreateUser([
      'administer search_api',
      'administer facets',
      'access administration pages',
      'administer blocks',
    ]);
    $this->drupalLogin($admin_user);
    $this->indexId = 'solr_search_index';

    // Check that the test index is on the admin overview
    $this->drupalGet('admin/config/search/search-api');
    $this->assertText('Test index');

    $this->setUpExampleStructure();
    $this->insertExampleContent();

    /** @var \Drupal\search_api\IndexInterface $index */
    $index = $this->indexStorage->load($this->indexId);
    $indexed_items = $index->indexItems();
    $this->assertEqual($indexed_items, 5, 'Five items are indexed.');

    // Create a facet, enable 'show numbers'.
    $this->createFacet('Owl', 'owl');
    $edit = ['widget' => 'links', 'widget_config[show_numbers]' => '1'];
    $this->drupalPostForm('admin/config/search/facets/owl/edit', $edit, $this->t('Save'));

    // Verify that the facet results are correct.
    $this->drupalGet('search-api-test-fulltext');
    $this->assertResponse(200);
    $this->assertText('item (3)');
    $this->assertText('article (2)');
    $this->assertText('Displaying 5 search results');
    $this->clickLinkPartialName('item');
    $this->assertResponse(200);
    $this->assertText('Displaying 3 search results');
  }

  /**
   * Tests creating a search server via the UI.
   */
  protected function createServer($server_id = '_test_server') {
    $this->serverId = $server_id;
    $server_name = 'Search API &{}<>! Server';
    $server_description = 'A >server< used for testing &.';
    $edit_path = 'admin/config/search/search-api/add-server';

    $this->drupalGet($edit_path);
    $this->assertResponse(200, 'Server add page exists');

    $edit = array(
      'name' => '',
      'status' => 1,
      'description' => 'A server used for testing.',
      'backend' => $this->serverBackend,
    );

    $this->drupalPostForm($edit_path, $edit, $this->t('Save'));
    $this->assertText($this->t('@name field is required.', array('@name' => $this->t('Server name'))));

    $edit = array(
      'name' => $server_name,
      'status' => 1,
      'description' => $server_description,
      'backend' => $this->serverBackend,
    );
    $this->drupalPostForm($edit_path, $edit, $this->t('Save'));
    $this->assertText($this->t('@name field is required.', array('@name' => $this->t('Machine-readable name'))));

    $edit = array(
      'name' => $server_name,
      'id' => $this->serverId,
      'status' => 1,
      'description' => $server_description,
      'backend' => $this->serverBackend,
    );

    $this->drupalPostForm(NULL, $edit, $this->t('Save'));

    $this->assertText($this->t('The server was successfully saved.'));
    $this->assertUrl('admin/config/search/search-api/server/' . $this->serverId, array(), 'Correct redirect to server page.');
    $this->assertHtmlEscaped($server_name);
    $this->assertHtmlEscaped($server_description);

    // Go back in and configure solr.
    $edit_path = 'admin/config/search/search-api/server/' . $this->serverId . '/edit';
    $this->drupalGet($edit_path);
    $edit = [
      'backend_config[host]' => 'localhost',
      'backend_config[port]' => '8983',
      'backend_config[path]' => '/solr',
      'backend_config[core]' => 'd8',
    ];
    $this->drupalPostForm(NULL, $edit, $this->t('Save'));

    $this->drupalGet('admin/config/search/search-api');
    $this->assertHtmlEscaped($server_name);
    $this->assertHtmlEscaped($server_description);
  }

  /**
   * Tests creating a search index via the UI.
   */
  protected function createIndex() {
    $settings_path = 'admin/config/search/search-api/add-index';
    $this->indexId = 'test_index';
    $index_description = 'An >index< used for &! tęsting.';
    $index_name = 'Search >API< test &!^* index';

    $this->drupalGet($settings_path);
    $this->assertResponse(200);
    $edit = array(
      'status' => 1,
      'description' => $index_description,
    );

    $this->drupalPostForm(NULL, $edit, $this->t('Save'));
    $this->assertText($this->t('@name field is required.', array('@name' => $this->t('Index name'))));
    $this->assertText($this->t('@name field is required.', array('@name' => $this->t('Machine-readable name'))));
    $this->assertText($this->t('@name field is required.', array('@name' => $this->t('Data sources'))));

    $edit = array(
      'name' => $index_name,
      'id' => $this->indexId,
      'status' => 1,
      'description' => $index_description,
      'server' => $this->serverId,
      'datasources[]' => array('entity:node'),
    );

    $this->drupalPostForm(NULL, $edit, $this->t('Save'));

    $this->assertText($this->t('The index was successfully saved.'));
    // @todo Make this work correctly.
    // $this->assertUrl($this->getIndexPath('fields/add'), array(), 'Correct redirect to index page.');
    $this->assertHtmlEscaped($index_name);

    $this->drupalGet($this->getIndexPath('edit'));
    $this->assertHtmlEscaped($index_name);

    $this->indexStorage->resetCache(array($this->indexId));
    /** @var $index \Drupal\search_api\IndexInterface */
    $index = $this->indexStorage->load($this->indexId);

    if ($this->assertTrue($index, 'Index was correctly created.')) {
      $this->assertEqual($index->label(), $edit['name'], 'Name correctly inserted.');
      $this->assertEqual($index->id(), $edit['id'], 'Index ID correctly inserted.');
      $this->assertTrue($index->status(), 'Index status correctly inserted.');
      $this->assertEqual($index->getDescription(), $edit['description'], 'Index ID correctly inserted.');
      $this->assertEqual($index->getServerId(), $edit['server'], 'Index server ID correctly inserted.');
      $this->assertEqual($index->getDatasourceIds(), $edit['datasources[]'], 'Index datasource id correctly inserted.');
    }
    else {
      // Since none of the other tests would work, bail at this point.
      throw new SearchApiException();
    }

    // Test the "Save and edit" button.
    $index2_id = 'test_index2';
    $edit['id'] = $index2_id;
    unset($edit['server']);
    $this->drupalPostForm($settings_path, $edit, $this->t('Save and edit'));

    $this->assertText($this->t('The index was successfully saved.'));
    $this->indexStorage->resetCache(array($index2_id));
    $index = $this->indexStorage->load($index2_id);
    $this->assertUrl($index->toUrl('add-fields'), array(), 'Correct redirect to index fields page.');

    $this->drupalGet('admin/config/search/search-api');
    $this->assertHtmlEscaped($index_name);
    $this->assertHtmlEscaped($index_description);
  }

  /**
   * Tests whether the tracking information is properly maintained.
   *
   * Will especially test the bundle option of the content entity datasource.
   */
  protected function checkContentEntityTracking() {
    // Initially there should be no tracked items, because there are no nodes.
    $tracked_items = $this->countTrackedItems();
    $this->assertEqual($tracked_items, 0, 'No items are tracked yet.');

    // Add two articles and a page.
    $article1 = $this->drupalCreateNode(array('type' => 'article'));
    $this->drupalCreateNode(array('type' => 'article'));
    $this->drupalCreateNode(array('type' => 'page'));

    // Those 3 new nodes should be added to the tracking table immediately.
    $tracked_items = $this->countTrackedItems();
    $this->assertEqual($tracked_items, 3, 'Three items are tracked.');

    $node_count = \Drupal::entityQuery('node')->count()->execute();
    $this->assertEqual($node_count, $tracked_items);

    // Test disabling the index.
    $settings_path = $this->getIndexPath('edit');
    $this->drupalGet($settings_path);
    $edit = array(
      'status' => FALSE,
      'datasource_configs[entity:node][bundles][default]' => 0,
      'datasource_configs[entity:node][bundles][selected][article]' => FALSE,
      'datasource_configs[entity:node][bundles][selected][page]' => FALSE,
    );
    $this->drupalPostForm(NULL, $edit, $this->t('Save'));
    $this->assertText($this->t('The index was successfully saved.'));

    $tracked_items = $this->countTrackedItems();
    $this->assertEqual($tracked_items, 0, 'No items are tracked.');

    // Test re-enabling the index.
    $this->drupalGet($settings_path);

    $edit = array(
      'status' => TRUE,
      'datasource_configs[entity:node][bundles][default]' => 0,
      'datasource_configs[entity:node][bundles][selected][article]' => TRUE,
      'datasource_configs[entity:node][bundles][selected][page]' => TRUE,
    );
    $this->drupalPostForm(NULL, $edit, $this->t('Save'));
    $this->assertText($this->t('The index was successfully saved.'));

    $tracked_items = $this->countTrackedItems();
    $this->assertEqual($tracked_items, 3, 'Three items are tracked.');

    // Uncheck "default" and don't select any bundles. This should remove all
    // items from the tracking table.
    $edit = array(
      'status' => TRUE,
      'datasource_configs[entity:node][bundles][default]' => 0,
      'datasource_configs[entity:node][bundles][selected][article]' => FALSE,
      'datasource_configs[entity:node][bundles][selected][page]' => FALSE,
    );
    $this->drupalPostForm($settings_path, $edit, $this->t('Save'));
    $this->assertText($this->t('The index was successfully saved.'));

    $tracked_items = $this->countTrackedItems();
    $this->assertEqual($tracked_items, 0, 'No items are tracked.');

    // Leave "default" unchecked and select the "article" bundle. This should
    // re-add the two articles to the tracking table.
    $edit = array(
      'status' => TRUE,
      'datasource_configs[entity:node][bundles][default]' => 0,
      'datasource_configs[entity:node][bundles][selected][article]' => TRUE,
      'datasource_configs[entity:node][bundles][selected][page]' => FALSE,
    );
    $this->drupalPostForm($settings_path, $edit, $this->t('Save'));
    $this->assertText($this->t('The index was successfully saved.'));

    $tracked_items = $this->countTrackedItems();
    $this->assertEqual($tracked_items, 2, 'Two items are tracked.');

    // Leave "default" unchecked and select only the "page" bundle. This should
    // result in only the page being present in the tracking table.
    $edit = array(
      'status' => TRUE,
      'datasource_configs[entity:node][bundles][default]' => 0,
      'datasource_configs[entity:node][bundles][selected][article]' => FALSE,
      'datasource_configs[entity:node][bundles][selected][page]' => TRUE,
    );
    $this->drupalPostForm($settings_path, $edit, $this->t('Save'));
    $this->assertText($this->t('The index was successfully saved.'));

    $tracked_items = $this->countTrackedItems();
    $this->assertEqual($tracked_items, 1, 'One item is tracked.');

    // Check "default" again and select the "article" bundle. This shouldn't
    // change the tracking table, which should still only contain the page.
    $edit = array(
      'status' => TRUE,
      'datasource_configs[entity:node][bundles][default]' => 1,
      'datasource_configs[entity:node][bundles][selected][article]' => TRUE,
      'datasource_configs[entity:node][bundles][selected][page]' => FALSE,
    );
    $this->drupalPostForm($settings_path, $edit, $this->t('Save'));
    $this->assertText($this->t('The index was successfully saved.'));

    $tracked_items = $this->countTrackedItems();
    $this->assertEqual($tracked_items, 1, 'One item is tracked.');

    // Leave "default" checked but now select only the "page" bundle. This
    // should result in only the articles being tracked.
    $edit = array(
      'status' => TRUE,
      'datasource_configs[entity:node][bundles][default]' => 1,
      'datasource_configs[entity:node][bundles][selected][article]' => FALSE,
      'datasource_configs[entity:node][bundles][selected][page]' => TRUE,
    );
    $this->drupalPostForm($settings_path, $edit, $this->t('Save'));
    $this->assertText($this->t('The index was successfully saved.'));

    $tracked_items = $this->countTrackedItems();
    $this->assertEqual($tracked_items, 2, 'Two items are tracked.');

    // Delete an article. That should remove it from the item table.
    $article1->delete();

    $tracked_items = $this->countTrackedItems();
    $this->assertEqual($tracked_items, 1, 'One item is tracked.');

    // Go back to the default setting to continue the test.
    $edit = array(
      'status' => TRUE,
      'datasource_configs[entity:node][bundles][default]' => 1,
      'datasource_configs[entity:node][bundles][selected][article]' => FALSE,
      'datasource_configs[entity:node][bundles][selected][page]' => FALSE,
    );
    $this->drupalPostForm($settings_path, $edit, $this->t('Save'));

    $tracked_items = $this->countTrackedItems();
    $this->assertEqual($tracked_items, 2, 'Two items are tracked.');
    $node_count = \Drupal::entityQuery('node')->count()->execute();
    $this->assertEqual($node_count, $tracked_items, 'All nodes are correctly tracked by the index.');
  }

  /**
   * Counts the number of tracked items in the test index.
   *
   * @return int
   *   The number of tracked items in the test index.
   */
  protected function countTrackedItems() {
    return $this->getIndex()->getTrackerInstance()->getTotalItemsCount();
  }

  /**
   * Counts the number of unindexed items in the test index.
   *
   * @return int
   *   The number of unindexed items in the test index.
   */
  protected function countRemainingItems() {
    return $this->getIndex()->getTrackerInstance()->getRemainingItemsCount();
  }

  /**
   * Changes the index's server and checks if it reacts correctly.
   *
   * The expected behavior is that, when an index's server is changed, all of
   * the index's items should be removed from the previous server and marked as
   * "unindexed" in the tracker.
   */
  protected function changeIndexServer() {
    $this->indexStorage->resetCache(array($this->indexId));
    /** @var $index \Drupal\search_api\IndexInterface */
    $index = $this->indexStorage->load($this->indexId);

    $node_count = \Drupal::entityQuery('node')->count()->execute();
    $this->assertEqual($node_count, $this->countTrackedItems(), 'All nodes are correctly tracked by the index.');

    // Index all remaining items on the index.
    $index->indexItems();

    $remaining_items = $this->countRemainingItems();
    $this->assertEqual($remaining_items, 0, 'All items have been successfully indexed.');

    // Create a second search server.
    $this->createServer('test_server_2');

    // Change the index's server to the new one.
    $settings_path = $this->getIndexPath('edit');
    $edit = array(
      'server' => $this->serverId,
    );
    $this->drupalPostForm($settings_path, $edit, $this->t('Save'));

    // After saving the new index, we should have called reindex.
    $remaining_items = $this->countRemainingItems();
    $this->assertEqual($remaining_items, $node_count, 'All items still need to be indexed.');
  }

  /**
   * Retrieves test index.
   *
   * @return \Drupal\search_api\IndexInterface
   *   The test index.
   */
  protected function getIndex() {
    return Index::load($this->indexId);
  }

  /**
   * Ensures that all occurrences of the string are properly escaped.
   *
   * This makes sure that the string is only mentioned in an escaped version and
   * is never double escaped.
   *
   * @param string $string
   *   The raw string to check for.
   */
  protected function assertHtmlEscaped($string) {
    $this->assertRaw(Html::escape($string));
    $this->assertNoRaw(Html::escape(Html::escape($string)));
    $this->assertNoRaw($string);
  }

}
