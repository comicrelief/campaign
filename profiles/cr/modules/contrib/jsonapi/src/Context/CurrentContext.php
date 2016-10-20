<?php

namespace Drupal\jsonapi\Context;

use Drupal\jsonapi\Configuration\ResourceManagerInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

/**
 * Class CurrentContext.
 *
 * Service for accessing information about the current JSON API request.
 *
 * @package \Drupal\jsonapi\Context
 */
class CurrentContext implements CurrentContextInterface {

  /**
   * The current route.
   *
   * @var \Symfony\Component\Routing\Route
   */
  protected $currentRoute;

  /**
   * The resource manager.
   *
   * @var \Drupal\jsonapi\Configuration\ResourceManagerInterface
   */
  protected $resourceManager;

  /**
   * The current resource config.
   *
   * @var \Drupal\jsonapi\Configuration\ResourceConfigInterface
   */
  protected $resourceConfig;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Creates a CurrentContext object.
   *
   * @param \Drupal\jsonapi\Configuration\ResourceManagerInterface $resource_manager
   *   The resource manager service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(ResourceManagerInterface $resource_manager, RequestStack $request_stack) {
    $this->resourceManager = $resource_manager;
    $this->currentRequest = $request_stack->getCurrentRequest();
    if ($route = $this->currentRequest->get(RouteObjectInterface::ROUTE_OBJECT)) {
      $this->setCurrentRoute($route);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function fromRequest(Request $request) {
    $this->currentRequest = $request;
  }

  /**
   * {@inheritdoc}
   */
  public function getResourceConfig() {
    if (!isset($this->resourceConfig)) {
      $entity_type_id = $this->getCurrentRoute()->getRequirement('_entity_type');
      $bundle_id = $this->getCurrentRoute()->getRequirement('_bundle');
      $this->resourceConfig = $this->resourceManager
        ->get($entity_type_id, $bundle_id);
    }

    return $this->resourceConfig;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentRoute() {
    return $this->currentRoute;
  }

  /**
   * {@inheritdoc}
   */
  public function setCurrentRoute(Route $route) {
    return $this->currentRoute = $route;
  }

  /**
   * {@inheritdoc}
   */
  public function getResourceManager() {
    return $this->resourceManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getJsonApiParameter($parameter_key) {
    $params = $this->currentRequest->attributes->get('_json_api_params');
    return (isset($params[$parameter_key])) ? $params[$parameter_key] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function hasExtension($extension_name) {
    return in_array($extension_name, $this->getExtensions());
  }

  /**
   * {@inheritdoc}
   */
  public function getExtensions() {
    $content_type_header = $this->currentRequest->headers->get('Content-Type');
    if (preg_match('/ext="([^"]+)"/i', $content_type_header, $match)) {
      $extensions = array_map('trim', explode(',', $match[1]));
      return $extensions;
    }
    return [];
  }


}
