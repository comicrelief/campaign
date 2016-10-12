<?php

namespace Drupal\yamlform;

use Drupal\Component\Plugin\FallbackPluginManagerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\CategorizingPluginManagerTrait;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides a plugin manager for form element plugins.
 *
 * @see hook_yamlform_element_info_alter()
 * @see \Drupal\yamlform\Annotation\YamlFormElement
 * @see \Drupal\yamlform\YamlFormElementInterface
 * @see \Drupal\yamlform\YamlFormElementBase
 * @see plugin_api
 */
class YamlFormElementManager extends DefaultPluginManager implements FallbackPluginManagerInterface, YamlFormElementManagerInterface {

  use CategorizingPluginManagerTrait;

  /**
   * List of already instantiated form element plugins.
   *
   * @var array
   */
  protected $instances = [];

  /**
   * Constructs a new YamlFormElementManager.
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
    parent::__construct('Plugin/YamlFormElement', $namespaces, $module_handler, 'Drupal\yamlform\YamlFormElementInterface', 'Drupal\yamlform\Annotation\YamlFormElement');

    $this->alterInfo('yamlform_element_info');
    $this->setCacheBackend($cache_backend, 'yamlform_element_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function getFallbackPluginId($plugin_id, array $configuration = []) {
    return 'yamlform_element';
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = []) {
    // If configuration is empty create a single reusable instance for each
    // Form element plugin.
    if (empty($configuration)) {
      if (!isset($this->instances[$plugin_id])) {
        $this->instances[$plugin_id] = parent::createInstance($plugin_id, $configuration);
      }
      return $this->instances[$plugin_id];
    }
    else {
      return parent::createInstance($plugin_id, $configuration);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getInstances() {
    $plugin_definitions = $this->getDefinitions();

    // If all the plugin definitions are initialize returned the cached
    // instances.
    if (count($plugin_definitions) == count($this->instances)) {
      return $this->instances;
    }

    // Initialize and return all plugin instances.
    foreach ($plugin_definitions as $plugin_id => $plugin_definition) {
      $this->createInstance($plugin_id);
    }

    return $this->instances;
  }

  /**
   * {@inheritdoc}
   */
  public function invokeMethod($method, array &$element, &$context1 = NULL, &$context2 = NULL) {
    // Make sure element has a #type.
    if (!isset($element['#type'])) {
      return NULL;
    }

    $plugin_id = $this->getElementPluginId($element);

    /** @var \Drupal\yamlform\YamlFormElementInterface $yamlform_element */
    $yamlform_element = $this->createInstance($plugin_id);
    return $yamlform_element->$method($element, $context1, $context2);
  }

  /**
   * {@inheritdoc}
   */
  public function getElementPluginId(array $element) {
    if (isset($element['#type'])) {
      if ($this->hasDefinition($element['#type'])) {
        return $element['#type'];
      }
      elseif ($this->hasDefinition('yamlform_' . $element['#type'])) {
        return 'yamlform_' . $element['#type'];
      }
    }
    elseif (isset($element['#markup'])) {
      return 'yamlform_markup';
    }

    return $this->getFallbackPluginId(NULL);
  }

  /**
   * {@inheritdoc}
   */
  public function getElementInstance(array $element) {
    $plugin_id = $this->getElementPluginId($element);
    return $this->createInstance($plugin_id, $element);
  }

  /**
   * {@inheritdoc}
   */
  public function getSortedDefinitions(array $definitions = NULL, $label_key = 'label') {
    $definitions = isset($definitions) ? $definitions : $this->getDefinitions();
    uasort($definitions, function ($a, $b) use ($label_key) {
      return strnatcasecmp($a['category'] . '-' . $a[$label_key], $b['category'] . '-' . $b[$label_key]);
    });
    return $definitions;
  }

}
