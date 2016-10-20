<?php

namespace Drupal\jsonapi\Routing;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Routing\Enhancer\RouteEnhancerInterface;
use Drupal\jsonapi\Routing\Param\OffsetPage;
use Drupal\jsonapi\Routing\Param\Filter;
use Drupal\jsonapi\Routing\Param\Sort;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

/**
 * Class JsonApiParamEnhancer.
 *
 * @package Drupal\jsonapi\Routing
 */
class JsonApiParamEnhancer implements RouteEnhancerInterface {

  /**
   * The field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $fieldManager;

  /**
   * Instantiates a JsonApiParamEnhancer object.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $field_manager
   *   The field manager.
   */
  public function __construct(EntityFieldManagerInterface $field_manager) {
    $this->fieldManager = $field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(Route $route) {
    // This enhancer applies to the JSON API routes.
    return $route->getDefault(RouteObjectInterface::CONTROLLER_NAME) == Routes::FRONT_CONTROLLER;
  }

  /**
   * {@inheritdoc}
   */
  public function enhance(array $defaults, Request $request) {
    $options = [];
    if ($request->query->has('filter')) {
      $entity_type_id = $defaults[RouteObjectInterface::ROUTE_OBJECT]->getRequirement('_entity_type');
      $options['filter'] = new Filter($request->query->get('filter'), $entity_type_id, $this->fieldManager);
    }
    if ($request->query->has('sort')) {
      $options['sort'] = new Sort($request->query->get('sort'));
    }
    if ($request->query->has('page')) {
      $options['page'] = new OffsetPage($request->query->get('page'), 50);
    }
    else {
      $options['page'] = new OffsetPage(['start' => 0, 'size' => 50], 50);
    }
    $defaults['_json_api_params'] = $options;
    return $defaults;
  }

}
