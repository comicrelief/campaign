<?php

namespace Drupal\Tests\search_api_solr\Kernel\Processor;

use Drupal\search_api\Entity\Server;
use Symfony\Component\Yaml\Yaml;

/**
 * Tests the "Hierarchy" processor.
 *
 * @see \Drupal\search_api\Plugin\search_api\processor\AddHierarchy
 *
 * @group search_api_solr
 *
 * @coversDefaultClass \Drupal\search_api\Plugin\search_api\processor\AddHierarchy
 */
class AddHierarchyTest extends \Drupal\Tests\search_api\Kernel\Processor\AddHierarchyTest {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'search_api_solr',
    'search_api_solr_test',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp($processor = NULL) {
    parent::setUp();

    $this->server = Server::create(
      Yaml::parse(file_get_contents(
        drupal_get_path('module', 'search_api_solr_test') . '/config/install/search_api.server.solr_search_server.yml'
      ))
    );
    $this->server->save();

    $this->index->setServer($this->server);
    $this->index->save();

    $index_storage = $this->container
      ->get('entity_type.manager')
      ->getStorage('search_api_index');
    $index_storage->resetCache(array($this->index->id()));
    $this->index = $index_storage->load($this->index->id());
  }

  /**
   * {@inheritdoc}
   */
  protected function indexItems() {
    $index_status = parent::indexItems();
    sleep(2);
    return $index_status;
  }

}
