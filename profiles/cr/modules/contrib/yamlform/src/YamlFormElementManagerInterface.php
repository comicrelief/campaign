<?php

namespace Drupal\yamlform;

use Drupal\Component\Plugin\CategorizingPluginManagerInterface;
use Drupal\Component\Plugin\FallbackPluginManagerInterface;
use Drupal\Component\Plugin\Discovery\CachedDiscoveryInterface;
use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Collects available form elements.
 */
interface YamlFormElementManagerInterface extends PluginManagerInterface, CachedDiscoveryInterface, FallbackPluginManagerInterface, CategorizingPluginManagerInterface {

  /**
   * Get all available form element plugin instances.
   *
   * @return \Drupal\yamlform\YamlFormElementInterface[]
   *   An array of all available form element plugin instances.
   */
  public function getInstances();

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
  public function invokeMethod($method, array &$element, &$context1 = NULL, &$context2 = NULL);

  /**
   * Is an element's plugin id.
   *
   * @param array $element
   *   A element.
   *
   * @return string
   *   An element's $type has a corresponding plugin id, else
   *   fallback 'element' plugin id.
   */
  public function getElementPluginId(array $element);

  /**
   * Get a form element plugin instance for an element.
   *
   * @param array $element
   *   An associative array containing an element with a #type property.
   *
   * @return \Drupal\yamlform\YamlFormElementInterface
   *   A form element plugin instance
   */
  public function getElementInstance(array $element);

}
