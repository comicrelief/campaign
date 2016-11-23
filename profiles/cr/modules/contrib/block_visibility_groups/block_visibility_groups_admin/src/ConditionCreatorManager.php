<?php

namespace Drupal\block_visibility_groups_admin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Routing\RouteMatch;

/**
 * A Plugin.
 */
class ConditionCreatorManager extends DefaultPluginManager {

  /**
   * Constructor.
   *
   * @param \Traversable $namespaces
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/ConditionCreator', $namespaces, $module_handler, 'Drupal\block_visibility_groups_admin\Plugin\ConditionCreatorInterface', 'Drupal\block_visibility_groups_admin\Annotation\ConditionCreator');
    $this->alterInfo('block_visibility_condition_creator');
    $this->setCacheBackend($cache_backend, 'block_visibility_groups_admin:creator');
  }

  /**
   * @param string $plugin_id
   * @param array $configuration
   *
   * @return object
   * @throws \Exception
   */
  public function createInstance($plugin_id, array $configuration = array()) {
    if (empty($configuration['route_name'])) {
      // @todo Also check for parameters?
      throw new \Exception('Route name is require configuration for GroupCreatorManager');
    }
    $route_name = $configuration['route_name'];
    /** @var \Drupal\Core\Routing\RouteProvider $route_provider */
    $route_provider = \Drupal::getContainer()->get('router.route_provider');
    $configuration['route'] = new RouteMatch($route_name, $route_provider->getRouteByName($route_name));
    unset($configuration['route_name']);
    return parent::createInstance($plugin_id, $configuration);
  }

}
