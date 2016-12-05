<?php

namespace Drupal\Tests\jsonapi\Unit\Context;

use Drupal\Tests\UnitTestCase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

use Drupal\jsonapi\Context\FieldResolver;
use Drupal\jsonapi\Context\CurrentContextInterface;
use Drupal\jsonapi\Configuration\ResourceConfigInterface;

/**
 * Class FieldResolverTest.
 *
 * @package \Drupal\jsonapi\Test\Unit
 *
 * @coversDefaultClass \Drupal\jsonapi\Context\FieldResolver
 *
 * @group jsonapi
 */
class FieldResolverTest extends UnitTestCase {

  /**
   * A mock for the current context service.
   *
   * @var \Drupal\jsonapi\Context\CurrentContextInterface
   */
  protected $currentContext;

  /**
   * A mock for the entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $fieldManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $current_context = $this->prophesize(CurrentContextInterface::class);

    $resource_config = $this->prophesize(ResourceConfigInterface::class);
    $resource_config->getEntityTypeId()->willReturn('lorem');
    $current_context->getResourceConfig()->willReturn($resource_config);

    $this->currentContext = $current_context->reveal();
  }

  /**
   * Expects a public field name to be expanded into a Drupal field name.
   *
   * @covers ::resolveInternal
   */
  public function testResolveInternalNested() {
    $field_manager = $this->prophesize(EntityFieldManagerInterface::class);
    $field_storage1 = $this->prophesize(FieldStorageDefinitionInterface::class);
    $field_storage1->getType()->willReturn('entity_reference');
    $field_storage1->getSetting('target_type')->willReturn('ipsum');
    $field_storage2 = $this->prophesize(FieldStorageDefinitionInterface::class);
    $field_storage2->getType()->willReturn('entity_reference');
    $field_storage2->getSetting('target_type')->willReturn('dolor');
    $field_manager->getFieldStorageDefinitions('lorem')
      ->willReturn(['host' => $field_storage1->reveal()]);
    $field_manager->getFieldStorageDefinitions('ipsum')
      ->willReturn(['nested' => $field_storage2->reveal()]);

    $original = 'host.nested.deep';
    $expected = 'host.entity.nested.entity.deep';
    $field_resolver = new FieldResolver($this->currentContext, $field_manager->reveal());

    $this->assertEquals($expected, $field_resolver->resolveInternal($original));
  }

  /**
   * Expects an error when an invalid field is provided.
   *
   * @covers ::resolveInternal
   *
   * @expectedException \Drupal\jsonapi\Error\SerializableHttpException
   */
  public function testResolveInternalError() {
    $field_manager = $this->prophesize(EntityFieldManagerInterface::class);
    $field_storage1 = $this->prophesize(FieldStorageDefinitionInterface::class);
    $field_storage1->getType()->willReturn('entity_reference');
    $field_storage1->getSetting('target_type')->willReturn('ipsum');
    $field_storage2 = $this->prophesize(FieldStorageDefinitionInterface::class);
    $field_storage2->getType()->willReturn('sid');
    $field_manager->getFieldStorageDefinitions('lorem')
      ->willReturn(['host' => $field_storage1->reveal()]);
    $field_manager->getFieldStorageDefinitions('ipsum')
      ->willReturn(['nested' => $field_storage2->reveal()]);

    $original = 'host.nested.deep';
    $not_expected = 'host.entity.nested.entity.deep';
    $field_resolver = new FieldResolver($this->currentContext, $field_manager->reveal());

    $this->assertEquals($not_expected, $field_resolver->resolveInternal($original));
  }

  /**
   * Expects an error when an invalid field is provided.
   *
   * @covers ::resolveInternal
   *
   * @expectedException \Drupal\jsonapi\Error\SerializableHttpException
   */
  public function testResolveInternalError2() {
    $field_manager = $this->prophesize(EntityFieldManagerInterface::class);
    $field_storage1 = $this->prophesize(FieldStorageDefinitionInterface::class);
    $field_storage1->getType()->willReturn('entity_reference');
    $field_storage1->getSetting('target_type')->willReturn('ipsum');
    $field_manager->getFieldStorageDefinitions('lorem')
      ->willReturn(['fail' => $field_storage1->reveal()]);

    $original = 'host.nested.deep';
    $not_expected = 'host.entity.nested.entity.deep';
    $field_resolver = new FieldResolver($this->currentContext, $field_manager->reveal());

    $this->assertEquals($not_expected, $field_resolver->resolveInternal($original));
  }

}
