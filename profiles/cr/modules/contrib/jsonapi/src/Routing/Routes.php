<?php

namespace Drupal\jsonapi\Routing;

use Drupal\Core\Authentication\AuthenticationCollectorInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\jsonapi\Plugin\JsonApiResourceManager;
use Drupal\jsonapi\Resource\DocumentWrapperInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Defines dynamic routes.
 */
class Routes implements ContainerInjectionInterface {

  /**
   * The front controller for the JSON API routes.
   *
   * All routes will use this callback to bootstrap the JSON API process.
   *
   * @var string
   */
  const FRONT_CONTROLLER = '\Drupal\jsonapi\RequestHandler::handle';

  /**
   * The resource manager interface.
   *
   * @var \Drupal\jsonapi\Plugin\JsonApiResourceManager
   */
  protected $resourcePluginManager;

  /**
   * The authentication collector.
   *
   * @var \Drupal\Core\Authentication\AuthenticationCollectorInterface
   */
  protected $authCollector;

  /**
   * List of providers.
   *
   * @var string[]
   */
  protected $providerIds;

  /**
   * Instantiates a Routes object.
   *
   * @param \Drupal\jsonapi\Plugin\JsonApiResourceManager $resource_plugin_manager
   *   The resource manager.
   * @param \Drupal\Core\Authentication\AuthenticationCollectorInterface $auth_collector
   *   The resource manager.
   */
  public function __construct(JsonApiResourceManager $resource_plugin_manager, AuthenticationCollectorInterface $auth_collector) {
    $this->resourcePluginManager = $resource_plugin_manager;
    $this->authCollector = $auth_collector;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /* @var \Drupal\jsonapi\Plugin\JsonApiResourceManager $resource_plugin_manager */
    $resource_plugin_manager = $container->get('plugin.manager.resource.processor');
    /* @var \Drupal\Core\Authentication\AuthenticationCollectorInterface $auth_collector */
    $auth_collector = $container->get('authentication_collector');
    return new static($resource_plugin_manager, $auth_collector);
  }

  /**
   * {@inheritdoc}
   */
  public function routes() {
    $collection = new RouteCollection();
    foreach ($this->resourcePluginManager->getDefinitions() as $plugin_id => $plugin_definition) {
      if (empty($plugin_definition['enabled'])) {
        continue;
      }
      $entity_type = $plugin_definition['entityType'];
      // For the entity type resources the bundle is NULL.
      $bundle = $plugin_definition['bundle'];
      $entity_type_has_bundle = $plugin_definition['hasBundle'];
      $partial_path = $plugin_definition['data']['partialPath'];
      $schema_partial_path = $plugin_definition['schema']['partialPath'];
      $route_keys = explode(':', $plugin_id);
      $route_key = end($route_keys) . '.';
      // Add the collection route.
      $defaults = [
        RouteObjectInterface::CONTROLLER_NAME => $plugin_definition['controller'],
      ];
      // Options that apply to all routes.
      $options = [
        '_auth' => $this->authProviderList(),
        '_is_jsonapi' => TRUE,
      ];

      // Collection endpoint, like /api/file/photo.
      $route_collection = (new Route($partial_path))
        ->addDefaults($defaults)
        ->setRequirement('_entity_type', $entity_type)
        ->setRequirement('_permission', $plugin_definition['permission'])
        ->setRequirement('_format', 'api_json')
        ->setRequirement('_custom_parameter_names', 'TRUE')
        ->setOption('serialization_class', DocumentWrapperInterface::class)
        ->setMethods(['GET', 'POST']);
      if ($bundle) {
        $route_collection->setRequirement('_bundle', $bundle);
      }
      $route_collection->addOptions($options);
      $collection->add($route_key . 'collection', $route_collection);

      // Individual endpoint, like /api/file/photo/123.
      $parameters = [$entity_type => ['type' => 'entity:' . $entity_type]];
      $route_individual = (new Route(sprintf('%s/{%s}', $partial_path, $entity_type)))
        ->addDefaults($defaults)
        ->setRequirement('_entity_type', $entity_type)
        ->setRequirement('_permission', $plugin_definition['permission'])
        ->setRequirement('_format', 'api_json')
        ->setRequirement('_custom_parameter_names', 'TRUE')
        ->setOption('parameters', $parameters)
        ->setOption('_auth', $this->authProviderList())
        ->setOption('serialization_class', DocumentWrapperInterface::class)
        ->setMethods(['GET', 'PATCH', 'DELETE']);
      if ($bundle) {
        $route_individual->setRequirement('_bundle', $bundle);
      }
      $route_individual->addOptions($options);
      $collection->add($route_key . 'individual', $route_individual);

      // Related resource, like /api/file/photo/123/comments.
      $route_related = (new Route(sprintf('%s/{%s}/{related}', $partial_path, $entity_type)))
        ->addDefaults($defaults)
        ->setRequirement('_entity_type', $entity_type)
        ->setRequirement('_permission', $plugin_definition['permission'])
        ->setRequirement('_format', 'api_json')
        ->setRequirement('_custom_parameter_names', 'TRUE')
        ->setOption('parameters', $parameters)
        ->setOption('_auth', $this->authProviderList())
        ->setMethods(['GET']);
      if ($bundle) {
        $route_related->setRequirement('_bundle', $bundle);
      }
      $route_related->addOptions($options);
      $collection->add($route_key . 'related', $route_related);

      // Related endpoint, like /api/file/photo/123/relationships/comments.
      $route_relationship = (new Route(sprintf('%s/{%s}/relationships/{related}', $partial_path, $entity_type)))
        ->addDefaults($defaults + ['_on_relationship' => TRUE])
        ->setRequirement('_entity_type', $entity_type)
        ->setRequirement('_permission', $plugin_definition['permission'])
        ->setRequirement('_format', 'api_json')
        ->setRequirement('_custom_parameter_names', 'TRUE')
        ->setOption('parameters', $parameters)
        ->setOption('_auth', $this->authProviderList())
        ->setOption('serialization_class', EntityReferenceFieldItemList::class)
        ->setMethods(['GET', 'POST', 'PATCH', 'DELETE']);
      if ($bundle) {
        $route_relationship->setRequirement('_bundle', $bundle);
      }
      $route_relationship->addOptions($options);
      $collection->add($route_key . 'relationship', $route_relationship);

      // Schema for /api/file/photo.
      $route_collection_schema = (new Route($schema_partial_path))
        ->addDefaults([
          '_controller' => '\Drupal\jsonapi\Controller\SchemaController::entityCollectionSchema',
          'typed_data_id' => 'entity:' . $entity_type . (($entity_type_has_bundle) ? ':' . $bundle : ''),
        ])
        ->setRequirement('_permission', $plugin_definition['permission'])
        ->setOption('_auth', $this->authProviderList())
        ->setMethods(['GET']);
      $collection->add($route_key . 'schema', $route_collection_schema);

      // Schema for /api/file/photo/1234.
      $route_individual_schema = (new Route(sprintf('%s/{%s}', $schema_partial_path, $entity_type)))
        ->addDefaults([
          '_controller' => '\Drupal\jsonapi\Controller\SchemaController::entitySchema',
          'typed_data_id' => 'entity:' . $entity_type . (($entity_type_has_bundle) ? ':' . $bundle : ''),
        ])
        ->setRequirement('_permission', $plugin_definition['permission'])
        ->setOption('_auth', $this->authProviderList())
        ->setMethods(['GET']);
      $collection->add($route_key . 'individual.schema', $route_individual_schema);
    }

    return $collection;
  }

  /**
   * Build a list of authentication provider ids.
   *
   * @return string[]
   *   The list of IDs.
   */
  protected function authProviderList() {
    if (isset($this->providerIds)) {
      return $this->providerIds;
    }
    $this->providerIds = array_keys($this->authCollector->getSortedProviders());
    return $this->providerIds;
  }

}
