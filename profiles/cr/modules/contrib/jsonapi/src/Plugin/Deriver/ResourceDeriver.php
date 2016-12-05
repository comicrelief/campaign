<?php

namespace Drupal\jsonapi\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\jsonapi\Configuration\ResourceManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource plugin definition for every entity type's bundle.
 *
 * @see \Drupal\jsonapi\Plugin\jsonapi\resource\BundleResource
 */
class ResourceDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * List of derivative definitions.
   *
   * @var array
   */
  protected $derivatives;

  /**
   * The resource manager.
   *
   * @var \Drupal\jsonapi\Configuration\ResourceManagerInterface
   */
  protected $resourceManager;

  /**
   * Constructs an ResourceDeriver object.
   *
   * @param \Drupal\jsonapi\Configuration\ResourceManagerInterface $resource_manager
   *   The entity manager.
   */
  public function __construct(ResourceManagerInterface $resource_manager) {
    $this->resourceManager = $resource_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    /* @var \Drupal\jsonapi\Configuration\ResourceManagerInterface $resource_manager */
    $resource_manager = $container->get('jsonapi.resource.manager');
    return new static($resource_manager);
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_definition) {
    if (isset($this->derivatives)) {
      return $this->derivatives;
    }
    $this->derivatives = [];
    // Add in the default plugin configuration and the resource type.
    /* @var \Drupal\jsonapi\Configuration\ResourceConfigInterface[] $resource_configs */
    $resource_configs = $this->resourceManager->all();
    foreach ($resource_configs as $resource) {
      $global_config = $resource->getGlobalConfig();
      $prefix = $global_config->get('prefix');
      $schema_prefix = $global_config->get('schema_prefix');
      $id = sprintf('%s.dynamic.%s', $prefix, $resource->getTypeName());
      $this->derivatives[$id] = [
        'id' => $id,
        'entityType' => $resource->getEntityTypeId(),
        'bundle' => $resource->getBundleId(),
        'hasBundle' => $this->resourceManager->hasBundle($resource->getEntityTypeId()),
        'type' => $resource->getTypeName(),
        'data' => [
          'prefix' => $prefix,
          'partialPath' => '/' . $prefix . $resource->getPath()
        ],
        'schema' => [
          'prefix' => $schema_prefix,
          'partialPath' => '/' . $schema_prefix . $resource->getPath()
        ]
      ];

      $this->derivatives[$id] += $base_definition;
    }
    return $this->derivatives;
  }

}
