<?php

namespace Drupal\yamlform;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\CategorizingPluginManagerTrait;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages form handler plugins.
 *
 * @see hook_yamlform_handler_info_alter()
 * @see \Drupal\yamlform\Annotation\YamlFormHandler
 * @see \Drupal\yamlform\YamlFormHandlerInterface
 * @see \Drupal\yamlform\YamlFormHandlerBase
 * @see plugin_api
 */
class YamlFormHandlerManager extends DefaultPluginManager implements YamlFormHandlerManagerInterface {

  use CategorizingPluginManagerTrait {
    getSortedDefinitions as traitGetSortedDefinitions;
    getGroupedDefinitions as traitGetGroupedDefinitions;
  }

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

  /**
   * {@inheritdoc}
   */
  public function getSortedDefinitions(array $definitions = NULL) {
    // Sort the plugins first by category, then by label.
    $definitions = $this->traitGetSortedDefinitions($definitions);
    // Do not display the 'broken' plugin in the UI.
    unset($definitions['broken']);
    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupedDefinitions(array $definitions = NULL) {
    $definitions = $this->traitGetGroupedDefinitions($definitions);
    // Do not display the 'broken' plugin in the UI.
    unset($definitions[$this->t('Broken')]['broken']);
    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function getFallbackPluginId($plugin_id, array $configuration = []) {
    return 'broken';
  }

}
