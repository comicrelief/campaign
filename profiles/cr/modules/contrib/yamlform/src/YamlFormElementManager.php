<?php

/**
 * @file
 * Contains \Drupal\yamlform\YamlFormElementManager.
 */

namespace Drupal\yamlform;

use Drupal\Component\Plugin\FallbackPluginManagerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\yamlform\Plugin\YamlFormElement\ContainerBase;

/**
 * Manages YAML form element plugins.
 *
 * @see hook_yamlform_element_info_alter()
 * @see \Drupal\yamlform\Annotation\YamlFormElement
 * @see \Drupal\yamlform\YamlFormElementInterface
 * @see \Drupal\yamlform\YamlFormElementBase
 * @see plugin_api
 */
class YamlFormElementManager extends DefaultPluginManager implements FallbackPluginManagerInterface {

  /**
   * List of already instantiated YAML form element plugins.
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
    return 'element';
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = []) {
    // Cache a single reusable instance for each YAML form element plugin.
    // Not sure this the right way to limit plugins to single instances.
    if (!isset($this->instances[$plugin_id])) {
      $this->instances[$plugin_id] = parent::createInstance($plugin_id, $configuration);
    }
    return $this->instances[$plugin_id];
  }

  /**
   * Get all available YAML form element plugin instances.
   *
   * @return \Drupal\yamlform\YamlFormElementInterface[]
   *   An array of all available YAML form element plugin instances.
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
   * Invoke a method for specific FAPI element.
   *
   * @param string $method
   *   The method name.
   * @param array $element
   *   An associative array containing an element with a #type property.
   * @param mixed $context1
   *   (optional) An additional variable that is passed by reference.
   * @param mixed $context2
   *   (optional) An additional variable that is passed by reference. If more
   *   context needs to be provided to implementations, then this should be an
   *   associative array as described above.
   *
   * @return mixed|null
   *   Return result of the invoked method.  NULL will be returned if the
   *   element and/or method name does not exist.
   */
  public function invokeMethod($method, array &$element, &$context1 = NULL, &$context2 = NULL) {
    // Make sure element has #type.
    if (!isset($element['#type'])) {
      return NULL;
    }

    // See if element's $type has a corresponding plugin id else use the
    // fallback plugin id.
    $plugin_id = $this->hasDefinition($element['#type']) ? $element['#type'] : $this->getFallbackPluginId($element['#type']);

    /** @var \Drupal\yamlform\YamlFormElementInterface $yamlform_element */
    $yamlform_element = $this->createInstance($plugin_id);
    return $yamlform_element->$method($element, $context1, $context2);
  }

  /**
   * Is an element a container.
   *
   * @param array $element
   *   A element.
   *
   * @return bool
   *   TRUE is the element is a container.
   */
  public function isContainer(array $element) {
    if (!$element['#type']) {
      return FALSE;
    }

    $element_handler = $this->createInstance($element['#type']);
    return ($element_handler  && ($element_handler instanceof ContainerBase)) ? TRUE : FALSE;
  }

}
