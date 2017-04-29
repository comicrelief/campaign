<?php

/**
 * @file
 * Contains \Drupal\amp\Routing\AmpContext.
 */

namespace Drupal\amp\Routing;

use Drupal\amp\EntityTypeInfo;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\Routing\Route;

/**
 * Provides a helper class to determine whether the route is an amp one.
 */
class AmpContext {

  /**
   * Information about AMP-enabled content types.
   *
   * @var \Drupal\amp\EntityTypeInfo
   */
  protected $entityTypeInfo;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Construct a new amp context helper instance.
   *
   * @param \Drupal\amp\EntityTypeInfo $entity_type_info
   *   Information about AMP-enabled content types.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   */
  public function __construct(EntityTypeInfo $entity_type_info, RouteMatchInterface $route_match) {
    $this->entityTypeInfo = $entity_type_info;
    $this->routeMatch = $route_match;
  }

  /**
   * Determines whether the active route is an amp one.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   (optional) The route to determine whether it is an amp one. Per default
   *   this falls back to the route object on the active request.
   *
   * @return bool
   *   Returns TRUE if the route is an amp one, otherwise FALSE.
   */
  public function isAmpRoute(Route $route = NULL) {
    if (!$route) {
      $route = $this->routeMatch->getRouteObject();
      if (!$route) {
        return FALSE;
      }
    }

    // Check if the globally-defined AMP status has been changed to TRUE (it
    // is FALSE by default).
    if ($route->getOption('_amp_route')) {
      return TRUE;
    }

    // We only want to consider path with amp in the query string.
    if (!(isset($_GET['amp']))) {
      return FALSE;
    }

    // Get a list of content types that are AMP enabled.
    $enabled_types = $this->entityTypeInfo->getAmpEnabledTypes();
    // Load the current node.
    $node = $this->routeMatch->getParameter('node');
    // If we only got back the node ID, load the node.
    if (!is_object($node) && is_numeric($node)) {
      $node = \Drupal\node\Entity\Node::load($node);
    }
    // Check if we have a node. Will not be true on admin pages for example.
    if (is_object($node)) {
      $type = $node->getType();
      // Only show AMP routes for content that is AMP enabled.
      if ($enabled_types[$type] === $type) {
        return TRUE;
      }
    }

    return FALSE;
  }
}
