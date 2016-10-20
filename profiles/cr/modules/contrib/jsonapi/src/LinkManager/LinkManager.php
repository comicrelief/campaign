<?php

namespace Drupal\jsonapi\LinkManager;

use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\jsonapi\Configuration\ResourceConfigInterface;
use Drupal\jsonapi\Error\SerializableHttpException;
use Drupal\jsonapi\Routing\Param\OffsetPage;
use Symfony\Cmf\Component\Routing\ChainRouterInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LinkManager.
 *
 * @package Drupal\jsonapi
 */
class LinkManager implements LinkManagerInterface {

  /**
   * @var \Symfony\Cmf\Component\Routing\ChainRouter
   */
  protected $router;

  /**
   * @var \Drupal\Core\Render\MetadataBubblingUrlGenerator
   */
  protected $urlGenerator;

  /**
   * Instantiates a LinkManager object.
   *
   * @param \Symfony\Cmf\Component\Routing\ChainRouterInterface $router
   *   The router.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The Url generator.
   */
  public function __construct(ChainRouterInterface $router, UrlGeneratorInterface $url_generator) {
    $this->router = $router;
    $this->urlGenerator = $url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityLink($entity_id, ResourceConfigInterface $resource_config, array $route_parameters, $key) {
    $route_parameters += [
      $resource_config->getEntityTypeId() => $entity_id,
      '_format' => 'api_json',
    ];
    $prefix = $resource_config->getGlobalConfig()->get('prefix');
    $route_key = sprintf('%s.dynamic.%s.%s', $prefix, $resource_config->getTypeName(), $key);
    return $this->urlGenerator->generateFromRoute($route_key, $route_parameters, ['absolute' => TRUE]);
  }

  /**
   * {@inheritdoc}
   */
  public function getRequestLink(Request $request, $query = NULL) {
    $query = $query ?: (array) $request->query->getIterator();
    $result = $this->router->matchRequest($request);
    $route_name = $result[RouteObjectInterface::ROUTE_NAME];
    /* @var \Symfony\Component\HttpFoundation\ParameterBag $raw_variables */
    $raw_variables = $result['_raw_variables'];
    $route_parameters = $raw_variables->all();
    $options = [
      'absolute' => TRUE,
      'query' => $query,
    ];
    return $this->urlGenerator->generateFromRoute($route_name, $route_parameters, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function getPagerLinks(Request $request, array $link_context = []) {
    $params = $request->get('_json_api_params');
    if ($page_param = $params[OffsetPage::KEY_NAME]) {
      /* @var \Drupal\jsonapi\Routing\Param\OffsetPage $page_param */
      $offset = $page_param->getOffset();
      $size = $page_param->getSize();
    }
    else {
      // Apply the defaults.
      $offset = 0;
      $size = OffsetPage::$maxSize;
    }
    if ($size <= 0) {
      throw new SerializableHttpException(400, sprintf('The page size needs to be a positive integer.'));
    }
    $query = (array) $request->query->getIterator();
    $links = [];
    // Check if this is not the last page.
    if ($link_context['has_next_page']) {
      $links['next'] = $this->getRequestLink($request, $this->getPagerQueries('next', $offset, $size, $query));
    }
    // Check if this is not the first page.
    if ($offset > 0) {
      $links['first'] = $this->getRequestLink($request, $this->getPagerQueries('first', $offset, $size, $query));
      $links['prev'] = $this->getRequestLink($request, $this->getPagerQueries('prev', $offset, $size, $query));
    }

    return $links;
  }

  /**
   * Get the query param array.
   *
   * @param string $link_id
   *   The name of the pagination link requested.
   * @param int $offset
   *   The starting index.
   * @param int $size
   *   The pagination page size.
   * @param array $query
   *   The query parameters.
   *
   * @return array The pagination query param array.
   * The pagination query param array.
   */
  protected function getPagerQueries($link_id, $offset, $size, $query = []) {
    $extra_query = [];
    switch ($link_id) {
      case 'next':
        $extra_query = [
          'page' => [
            'offset' => $offset + $size,
            'size' => $size,
          ],
        ];
        break;

      case 'first':
        $extra_query = [
          'page' => [
            'offset' => 0,
            'size' => $size,
          ],
        ];
        break;

      case 'prev':
        $extra_query = [
          'page' => [
            'offset' => max($offset - $size, 0),
            'size' => $size,
          ],
        ];
        break;
    }
    return array_merge($query, $extra_query);
  }

}
