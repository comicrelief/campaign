<?php

namespace Drupal\jsonapi\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\jsonapi\Resource\DocumentWrapperInterface;
use Drupal\jsonapi\Routing\Routes;

/**
 * Provides the JSON API Resource plugin manager.
 */
class JsonApiResourceManager extends DefaultPluginManager {

  /**
   * Default values for the plugin definition.
   *
   * @var array
   */
  protected $defaults = [
    'permission' => 'access content',
    'controller' => Routes::FRONT_CONTROLLER,
    'enabled' => TRUE,
  ];

  /**
   * Constructor for JsonApiResourceManager objects.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/jsonapi', $namespaces, $module_handler, 'Drupal\jsonapi\Plugin\JsonApiResourceInterface', 'Drupal\jsonapi\Annotation\JsonApiResource');

    $this->alterInfo('jsonapi_resource_info');
    $this->setCacheBackend($cache_backend, 'jsonapi_resource_plugins');
  }

}
