<?php

namespace Drupal\Tests\focal_point\Unit;

use Drupal\crop\CropStorageInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\focal_point\FocalPointManager;
use Drupal\Tests\UnitTestCase;

/**
 * @group Focal Point
 */
abstract class FocalPointUnitTestCase extends UnitTestCase {

  /**
   * Drupal container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * Focal point manager.
   *
   * @var \Drupal\focal_point\FocalPointManagerInterface
   */
  protected $focalPointManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $crop_storage = $this->prophesize(CropStorageInterface::class);
    $entity_type_manager = $this->prophesize(EntityTypeManager::class);
    $entity_type_manager->getStorage('crop')->willReturn($crop_storage);

    $this->container = $this->prophesize(ContainerInterface::class);
    $this->container->get('entity_type.manager')->willReturn($entity_type_manager);

    $this->focalPointManager = new FocalPointManager($entity_type_manager->reveal());
    $this->container->get('focal_point.manager')->willReturn($this->focalPointManager);

    \Drupal::setContainer($this->container->reveal());
  }

}
