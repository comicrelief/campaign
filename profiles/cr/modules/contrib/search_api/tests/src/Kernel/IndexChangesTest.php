<?php

namespace Drupal\Tests\search_api\Kernel;

use Drupal\entity_test\Entity\EntityTestMulRevChanged;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\Entity\Server;
use Drupal\search_api\Utility\Utility;
use Drupal\search_api_test\PluginTestTrait;
use Drupal\user\Entity\User;

/**
 * Tests correct reactions to changes for the index.
 *
 * @group search_api
 */
class IndexChangesTest extends KernelTestBase {

  use PluginTestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = array(
    'search_api',
    'search_api_test',
    'language',
    'user',
    'system',
    'entity_test',
  );

  /**
   * The search server used for testing.
   *
   * @var \Drupal\search_api\ServerInterface
   */
  protected $server;

  /**
   * The search index used for testing.
   *
   * @var \Drupal\search_api\IndexInterface
   */
  protected $index;

  /**
   * The test entity type used in the test.
   *
   * @var string
   */
  protected $testEntityTypeId = 'entity_test_mulrev_changed';

  /**
   * The task manager to use for the tests.
   *
   * @var \Drupal\search_api\Task\TaskManagerInterface
   */
  protected $taskManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->installSchema('search_api', array(
      'search_api_item',
    ));
    $this->installEntitySchema('entity_test_mulrev_changed');
    $this->installEntitySchema('search_api_task');
    $this->installEntitySchema('user');

    $this->taskManager = $this->container->get('search_api.task_manager');

    // Set tracking page size so tracking will work properly.
    \Drupal::configFactory()
      ->getEditable('search_api.settings')
      ->set('tracking_page_size', 100)
      ->save();

    User::create(array(
      'uid' => 1,
      'name' => 'root',
      'langcode' => 'en',
    ))->save();

    EntityTestMulRevChanged::create(array(
      'id' => 1,
      'name' => 'test 1',
    ))->save();

    // Create a test server.
    $this->server = Server::create(array(
      'name' => 'Test Server',
      'id' => 'test_server',
      'status' => 1,
      'backend' => 'search_api_test',
    ));
    $this->server->save();

    // Create a test index (but don't save it yet).
    $this->index = Index::create(array(
      'name' => 'Test Index',
      'id' => 'test_index',
      'status' => 1,
      'tracker_settings' => array(
        'default' => array(
          'plugin_id' => 'default',
          'settings' => array(),
        ),
      ),
      'datasource_settings' => array(
        'entity:user' => array(
          'plugin_id' => 'entity:user',
          'settings' => array(),
        ),
        'entity:entity_test_mulrev_changed' => array(
          'plugin_id' => 'entity:entity_test_mulrev_changed',
          'settings' => array(),
        ),
      ),
      'server' => $this->server->id(),
      'options' => array('index_directly' => FALSE),
    ));

    $this->taskManager->deleteTasks();
  }

  /**
   * Tests correct reactions when a new datasource is added.
   */
  public function testDatasourceAdded() {
    $this->index->set('datasource_settings', array(
      'entity:user' => array(
        'plugin_id' => 'entity:user',
        'settings' => array(),
      ),
    ));
    $this->index->save();

    $tracker = $this->index->getTrackerInstance();

    $expected = array(
      Utility::createCombinedId('entity:user', '1:en'),
    );
    $this->assertEquals($expected, $tracker->getRemainingItems());

    /** @var \Drupal\search_api\Datasource\DatasourceInterface $datasource */
    $datasource = $this->index->createPlugin('datasource', 'entity:entity_test_mulrev_changed');
    $this->index->addDatasource($datasource)->save();

    $this->taskManager->executeAllTasks();

    $expected = array(
      Utility::createCombinedId('entity:entity_test_mulrev_changed', '1:en'),
      Utility::createCombinedId('entity:user', '1:en'),
    );
    $remaining_items = $tracker->getRemainingItems();
    sort($remaining_items);
    $this->assertEquals($expected, $remaining_items);

    User::create(array(
      'uid' => 2,
      'name' => 'someone',
      'langcode' => 'en',
    ))->save();
    EntityTestMulRevChanged::create(array(
      'id' => 2,
      'name' => 'test 2',
    ))->save();

    $expected = array(
      Utility::createCombinedId('entity:entity_test_mulrev_changed', '1:en'),
      Utility::createCombinedId('entity:entity_test_mulrev_changed', '2:en'),
      Utility::createCombinedId('entity:user', '1:en'),
      Utility::createCombinedId('entity:user', '2:en'),
    );
    $remaining_items = $tracker->getRemainingItems();
    sort($remaining_items);
    $this->assertEquals($expected, $remaining_items);

    $this->getCalledMethods('backend');
    $indexed = $this->index->indexItems();
    $this->assertEquals(4, $indexed);
    $this->assertEquals(array('indexItems'), $this->getCalledMethods('backend'));

    $indexed_items = array_keys($this->getIndexedItems());
    sort($indexed_items);
    $this->assertEquals($expected, $indexed_items);
    $this->assertEquals(0, $tracker->getRemainingItemsCount());
  }

  /**
   * Tests correct reactions when a datasource is removed.
   */
  public function testDatasourceRemoved() {
    $info = array(
      'datasource_id' => 'entity:entity_test_mulrev_changed',
      'property_path' => 'id',
    );
    $this->index->addField(Utility::createField($this->index, 'id', $info));
    $this->index->save();

    $tracker = $this->index->getTrackerInstance();

    $expected = array(
      Utility::createCombinedId('entity:entity_test_mulrev_changed', '1:en'),
      Utility::createCombinedId('entity:user', '1:en'),
    );
    $remaining_items = $tracker->getRemainingItems();
    sort($remaining_items);
    $this->assertEquals($expected, $remaining_items);

    $this->getCalledMethods('backend');
    $indexed = $this->index->indexItems();
    $this->assertEquals(2, $indexed);
    $this->assertEquals(array('indexItems'), $this->getCalledMethods('backend'));

    $indexed_items = array_keys($this->getIndexedItems());
    sort($indexed_items);
    $this->assertEquals($expected, $indexed_items);
    $this->assertEquals(0, $tracker->getRemainingItemsCount());

    $this->index->removeDatasource('entity:entity_test_mulrev_changed')->save();

    $this->assertArrayNotHasKey('id', $this->index->getFields());

    $this->assertEquals(1, $tracker->getTotalItemsCount());

    $expected = array(
      Utility::createCombinedId('entity:user', '1:en'),
    );
    $indexed_items = array_keys($this->getIndexedItems());
    sort($indexed_items);
    $this->assertEquals($expected, $indexed_items);
    $this->assertEquals(array('updateIndex', 'deleteAllIndexItems'), $this->getCalledMethods('backend'));

    User::create(array(
      'uid' => 2,
      'name' => 'someone',
      'langcode' => 'en',
    ))->save();
    EntityTestMulRevChanged::create(array(
      'id' => 2,
      'name' => 'test 2',
    ))->save();

    $this->assertEquals(2, $tracker->getTotalItemsCount());

    $indexed = $this->index->indexItems();
    $this->assertGreaterThanOrEqual(1, $indexed);
    $this->assertEquals(array('indexItems'), $this->getCalledMethods('backend'));

    $expected = array(
      Utility::createCombinedId('entity:user', '1:en'),
      Utility::createCombinedId('entity:user', '2:en'),
    );
    $indexed_items = array_keys($this->getIndexedItems());
    sort($indexed_items);
    $this->assertEquals($expected, $indexed_items);
    $this->assertEquals(0, $tracker->getRemainingItemsCount());
  }

  /**
   * Tests correct reaction when the index's tracker changes.
   */
  public function testTrackerChange() {
    $this->index->save();

    /** @var \Drupal\search_api\Tracker\TrackerInterface $tracker */
    $tracker = $this->index->createPlugin('tracker', 'search_api_test');
    $this->index->setTracker($tracker)->save();

    $this->taskManager->executeAllTasks();

    $methods = $this->getCalledMethods('tracker');
    // Note: The initial "trackAllItemsUpdated" call comes from the test
    // backend, which marks the index for re-indexing every time it gets
    // updated.
    $expected = array(
      'trackAllItemsUpdated',
      'trackItemsInserted',
      'trackItemsInserted',
    );
    $this->assertEquals($expected, $methods);

    /** @var \Drupal\search_api\Tracker\TrackerInterface $tracker */
    $tracker = $this->index->createPlugin('tracker', 'default');
    $this->index->setTracker($tracker)->save();

    $this->taskManager->executeAllTasks();

    $methods = $this->getCalledMethods('tracker');
    $this->assertEquals(array('trackAllItemsDeleted'), $methods);
    $arguments = $this->getMethodArguments('tracker', 'trackAllItemsDeleted');
    $this->assertEquals(array(), $arguments);
  }

  /**
   * Tests correct reaction when a processor adding a property is removed.
   */
  public function testPropertyProcessorRemoved() {
    $processor = $this->container
      ->get('plugin.manager.search_api.processor')
      ->createInstance('add_url', array(
        '#index' => $this->index,
      ));
    $this->index->addProcessor($processor);

    $info = array(
      'datasource_id' => 'entity:entity_test_mulrev_changed',
      'property_path' => 'id',
    );
    $this->index->addField(Utility::createField($this->index, 'id', $info));
    $info = array(
      'property_path' => 'search_api_url',
    );
    $this->index->addField(Utility::createField($this->index, 'url', $info));

    $this->index->save();

    $fields = array_keys($this->index->getFields());
    sort($fields);
    $this->assertEquals(array('id', 'url'), $fields);

    $this->index->removeProcessor('add_url')->save();

    $fields = array_keys($this->index->getFields());
    $this->assertEquals(array('id'), $fields);
  }

  /**
   * Tests correct reaction when a bundle containing a property is removed.
   */
  public function testPropertyBundleRemoved() {
    entity_test_create_bundle('bundle1', NULL, 'entity_test_mulrev_changed');
    entity_test_create_bundle('bundle2', NULL, 'entity_test_mulrev_changed');

    $this->enableModules(array('field', 'text'));
    $this->installEntitySchema('field_storage_config');
    $this->installEntitySchema('field_config');
    $this->installConfig('field');

    FieldStorageConfig::create(array(
      'field_name' => 'field1',
      'entity_type' => 'entity_test_mulrev_changed',
      'type' => 'text',
    ))->save();
    FieldConfig::create(array(
      'field_name' => 'field1',
      'entity_type' => 'entity_test_mulrev_changed',
      'bundle' => 'bundle1',
    ))->save();
    FieldStorageConfig::create(array(
      'field_name' => 'field2',
      'entity_type' => 'entity_test_mulrev_changed',
      'type' => 'text',
    ))->save();
    FieldConfig::create(array(
      'field_name' => 'field2',
      'entity_type' => 'entity_test_mulrev_changed',
      'bundle' => 'bundle2',
    ))->save();

    $datasource_id = 'entity:entity_test_mulrev_changed';
    $datasource = $this->container
      ->get('plugin.manager.search_api.datasource')
      ->createInstance($datasource_id, array(
        '#index' => $this->index,
        'bundles' => array(
          'default' => TRUE,
          'selected' => array(),
        ),
      ));
    $this->index->setDatasources(array($datasource_id => $datasource));

    $info = array(
      'datasource_id' => $datasource_id,
      'property_path' => 'field1',
    );
    $this->index->addField(Utility::createField($this->index, 'field1', $info));
    $info = array(
      'datasource_id' => $datasource_id,
      'property_path' => 'field2',
    );
    $this->index->addField(Utility::createField($this->index, 'field2', $info));

    $this->index->save();

    $fields = array_keys($this->index->getFields());
    sort($fields);
    $this->assertEquals(array('field1', 'field2'), $fields);

    $this->index->getDatasource($datasource_id)->setConfiguration(array(
      'bundles' => array(
        'default' => TRUE,
        'selected' => array('bundle2'),
      ),
    ));
    $this->index->save();

    $fields = array_keys($this->index->getFields());
    $this->assertEquals(array('field1'), $fields);
  }

  /**
   * Tests correct behavior when a field ID is changed.
   */
  public function testFieldRenamed() {
    $datasource_id = 'entity:entity_test_mulrev_changed';
    $info = array(
      'datasource_id' => $datasource_id,
      'property_path' => 'name',
    );
    $field = Utility::createField($this->index, 'name', $info);
    $this->index->addField($field);
    $this->assertEquals(array(), $this->index->getFieldRenames());

    $this->index->renameField('name', 'name1');
    $this->assertEquals(array('name1' => $field), $this->index->getFields());
    $this->assertEquals(array('name' => 'name1'), $this->index->getFieldRenames());

    // Saving resets the field IDs.
    $this->index->save();
    $this->assertEquals(array(), $this->index->getFieldRenames());
    $this->assertEquals('name1', $this->index->getField('name1')->getOriginalFieldIdentifier());
  }

  /**
   * Retrieves the indexed items from the test backend.
   *
   * @return array
   *   The indexed items, keyed by their item IDs and containing associative
   *   arrays with their field values.
   */
  protected function getIndexedItems() {
    $key = 'search_api_test.backend.indexed.' . $this->index->id();
    return \Drupal::state()->get($key, array());
  }

}
