<?php

namespace Drupal\jsonapi\Configuration;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

/**
 * Class ResourceManager.
 *
 * @package Drupal\jsonapi
 */
class ResourceManager implements ResourceManagerInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The bundle manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $bundleManager;

  /**
   * The loaded resource config objects.
   *
   * @var \Drupal\jsonapi\Configuration\ResourceConfigInterface[]
   */
  protected $all = [];

  /**
   * Instantiates a ResourceManager object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundle_manager
   *   The bundle manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory interface.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $bundle_manager, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->bundleManager = $bundle_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function all() {
    if ($this->all) {
      return $this->all;
    }
    $entity_type_ids = array_keys($this->entityTypeManager->getDefinitions());
    foreach ($entity_type_ids as $entity_type_id) {
      // Add a ResourceConfig per bundle.
      $this->all = array_merge($this->all, array_map(function ($bundle) use ($entity_type_id) {
        $resource_config = new ResourceConfig($this->configFactory, $this->entityTypeManager);
        $resource_config->setEntityTypeId($entity_type_id);
        $resource_config->setBundleId($bundle);
        $resource_config->setPath(sprintf('/%s/%s', $entity_type_id, $bundle));
        $resource_config->setTypeName(sprintf('%s--%s', $entity_type_id, $bundle));
        return $resource_config;
      }, array_keys($this->bundleManager->getBundleInfo($entity_type_id))));
    }
    return $this->all;
  }

  /**
   * {@inheritdoc}
   */
  public function get($entity_type_id, $bundle_id) {
    if (empty($entity_type_id)) {
      throw new PreconditionFailedHttpException('Server error. The current route is malformed.');
    }
    foreach ($this->all() as $resource) {
      if ($resource->getEntityTypeId() == $entity_type_id && $resource->getBundleId() == $bundle_id) {
        return $resource;
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeManager() {
    return $this->entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function hasBundle($entity_type_id) {
    return (bool) $this->getEntityTypeManager()
      ->getDefinition($entity_type_id)
      ->getBundleEntityType();
  }


}
