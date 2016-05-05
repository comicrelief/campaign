<?php

/**
 * @file
 * Contains \Drupal\yamlform\YamlFormHandlerManager.
 */

namespace Drupal\yamlform;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages YAML form handler plugins.
 *
 * @see hook_yamlform_handler_info_alter()
 * @see \Drupal\yamlform\Annotation\YamlFormHandler
 * @see \Drupal\yamlform\YamlFormHandlerInterface
 * @see \Drupal\yamlform\YamlFormHandlerBase
 * @see plugin_api
 */
class YamlFormHandlerManager extends DefaultPluginManager {

  /**
   * Constructs a new YamlFormHandlerManager.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/YamlFormHandler', $namespaces, $module_handler, 'Drupal\yamlform\YamlFormHandlerInterface', 'Drupal\yamlform\Annotation\YamlFormHandler');

    $this->alterInfo('yamlform_handler_info');
    $this->setCacheBackend($cache_backend, 'yamlform_handler_plugins');
  }

}
