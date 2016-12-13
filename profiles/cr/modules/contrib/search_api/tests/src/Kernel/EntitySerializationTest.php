<?php

namespace Drupal\Tests\search_api\Kernel;

use Drupal\Component\Serialization\Yaml;
use Drupal\KernelTests\KernelTestBase;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\Entity\Server;

/**
 * Tests the serialization of the entities.
 *
 * @group search_api
 */
class EntitySerializationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array(
    'search_api',
    'search_api_test',
    'node',
    'user',
    'system',
  );

  /**
   * Tests that serialization of index entities doesn't lead to data loss.
   */
  public function testIndexSerialization() {
    // As our test index, just use the one from the DB Defaults module.
    $path = __DIR__ . '/../../../modules/search_api_db/search_api_db_defaults/config/optional/search_api.index.default_index.yml';
    $index_values = Yaml::decode(file_get_contents($path));
    $index = new Index($index_values, 'search_api_index');

    // Make some changes to the index to ensure they're saved, too.
    $field_helper = \Drupal::getContainer()->get('search_api.fields_helper');
    $field_info = array(
      'type' => 'date',
      'datasource_id' => 'entity:node',
      'property_path' => 'uid:entity:created',
    );
    $index->addField($field_helper->createField($index, 'test1', $field_info));
    $index->addDatasource($index->createPlugin('datasource', 'entity:user'));
    $index->addProcessor($index->createPlugin('processor', 'highlight'));
    $index->setTracker($index->createPlugin('tracker', 'search_api_test'));

    /** @var \Drupal\search_api\IndexInterface $serialized */
    $serialized = unserialize(serialize($index));

    $this->assertNotEmpty($serialized);
    $storage = \Drupal::entityTypeManager()->getStorage('search_api_index');
    $index->preSave($storage);
    $serialized->preSave($storage);
    $this->assertEquals($index->toArray(), $serialized->toArray());
  }

  /**
   * Tests that serialization of server entities doesn't lead to data loss.
   */
  public function testServerSerialization() {
    // As our test server, just use the one from the DB Defaults module.
    $path = __DIR__ . '/../../../modules/search_api_db/search_api_db_defaults/config/optional/search_api.server.default_server.yml';
    $values = Yaml::decode(file_get_contents($path));
    $server = new Server($values, 'search_api_server');

    $serialized = unserialize(serialize($server));

    $this->assertNotEmpty($serialized);
    $this->assertEquals($server, $serialized);
  }

}
