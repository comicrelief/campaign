<?php


namespace Drupal\jsonapi\LinkManager;

use Drupal\jsonapi\Configuration\ResourceConfigInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LinkManagerInterface.
 *
 * @package Drupal\jsonapi
 */
interface LinkManagerInterface {

  /**
   * Gets a link for the entity.
   *
   * @param int $entity_id
   *   The entity ID to generate the link for. Note: Depending on the
   *   configuration this might be the UUID as well.
   * @param \Drupal\jsonapi\Configuration\ResourceConfigInterface $resource_config
   *   The resource configuration.
   * @param array $route_parameters
   *   Parameters for the route generation.
   * @param string $key
   *   A key to build the route identifier.
   *
   * @return string
   *   The URL string.
   */
  public function getEntityLink($entity_id, ResourceConfigInterface $resource_config, array $route_parameters, $key);

  /**
   * Get the full URL for a given request object.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   * @param array|NULL $query
   *   The query parameters to use. Leave it empty to get the query from the
   *   request object.
   *
   * @return string
   *   The full URL.
   */
  public function getRequestLink(Request $request, $query = NULL);

  /**
   * Get the full URL for a given request object.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   * @param array
   *   An associative array with extra data to build the links.
   *
   * @throws \Drupal\jsonapi\Error\SerializableHttpException
   *   When the offset and size are invalid.
   *
   * @return string
   *   The full URL.
   */
  public function getPagerLinks(Request $request, array $link_context = []);

}
