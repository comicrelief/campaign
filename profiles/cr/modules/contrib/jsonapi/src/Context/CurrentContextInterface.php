<?php

namespace Drupal\jsonapi\Context;

use Drupal\jsonapi\Configuration\ResourceConfigInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

/**
 * Interface CurrentContextInterface.
 *
 * An interface for accessing contextual information for the current request.
 *
 * @package \Drupal\jsonapi\Context
 */
interface CurrentContextInterface {

  /**
   * Returns a ResouceConfig for the current request.
   *
   * @return \Drupal\jsonapi\Configuration\ResourceConfigInterface
   *   The ResourceConfig object corresponding to the current request.
   */
  public function getResourceConfig();

  /**
   * Returns the current route match.
   *
   * @return \Symfony\Component\Routing\Route
   *   The currently matched route.
   */
  public function getCurrentRoute();

  /**
   * Returns the current route match.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route to set.
   */
  public function setCurrentRoute(Route $route);

  /**
   * Returns the resource manager.
   *
   * @return \Drupal\jsonapi\Configuration\ResourceManagerInterface
   */
  public function getResourceManager();

  /**
   * Get a value by key from the _json_api_params route parameter.
   *
   * @param string $parameter_key
   *   The key by which to retrieve a route parameter.
   *
   * @return mixed
   *   The JSON API provided parameter.
   */
  public function getJsonApiParameter($parameter_key);

  /**
   * Configures the current context from a request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   */
  public function fromRequest(Request $request);

  /**
   * Determines, whether the JSONAPI extension was requested.
   *
   * @todo Find a better place for such a JSONAPI derived information.
   *
   * @param string $extension_name
   *   The extension name.
   *
   * @return bool
   *   Returns TRUE, if the extension has been found.
   */
  public function hasExtension($extension_name);

  /**
   * Returns a list of requested extensions.
   *
   * @return string[]
   *   The extension names.
   */
  public function getExtensions();

}
