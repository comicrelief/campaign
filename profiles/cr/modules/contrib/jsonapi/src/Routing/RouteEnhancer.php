<?php

namespace Drupal\jsonapi\Routing;

use Drupal\Core\Routing\Enhancer\RouteEnhancerInterface;
use Drupal\jsonapi\Error\SerializableHttpException;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

/**
 * Class RouteEnhancer.
 *
 * @package Drupal\jsonapi\Routing
 */
class RouteEnhancer implements RouteEnhancerInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(Route $route) {
    return (bool) $route->getRequirement('_bundle') && (bool) $route->getRequirement('_entity_type');
  }

  /**
   * {@inheritdoc}
   */
  public function enhance(array $defaults, Request $request) {
    $route = $defaults[RouteObjectInterface::ROUTE_OBJECT];
    $entity_type = $route->getRequirement('_entity_type');
    if (!isset($defaults[$entity_type]) || !($entity = $defaults[$entity_type])) {
      return $defaults;
    }
    $retrieved_bundle = $entity->bundle();
    $configured_bundle = $route->getRequirement('_bundle');
    if ($retrieved_bundle != $configured_bundle) {
      // If the bundle in the loaded entity does not match the bundle in the
      // route configuration (that comes from the resource_config), then throw
      // an exception.
      throw new SerializableHttpException(404, sprintf('The loaded entity bundle (%s) does not match the configured resource (%s).', $retrieved_bundle, $configured_bundle));
    }
    return $defaults;
  }

}
