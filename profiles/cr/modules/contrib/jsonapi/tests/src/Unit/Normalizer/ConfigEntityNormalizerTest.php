<?php

namespace Drupal\Tests\jsonapi\Unit\Normalizer;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\jsonapi\Configuration\ResourceConfigInterface;
use Drupal\jsonapi\Configuration\ResourceManagerInterface;
use Drupal\jsonapi\Normalizer\ConfigEntityNormalizer;
use Drupal\jsonapi\LinkManager\LinkManagerInterface;
use Drupal\jsonapi\Context\CurrentContextInterface;
use Drupal\jsonapi\Normalizer\ScalarNormalizer;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Symfony\Component\Routing\Route;
use Symfony\Component\Serializer\Serializer;

/**
 * Class DocumentRootNormalizerTest.
 *
 * @package Drupal\Tests\serialization\Unit\Normalizer
 *
 * @coversDefaultClass \Drupal\jsonapi\Normalizer\DocumentRootNormalizer
 *
 * @group jsonapi
 */
class ConfigEntityNormalizerTest extends UnitTestCase {

  /**
   * The normalizer under test.
   *
   * @var \Drupal\jsonapi\Normalizer\DocumentRootNormalizer
   */
  protected $normalizer;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $link_manager = $this->prophesize(LinkManagerInterface::class);
    $current_context_manager = $this->prophesize(CurrentContextInterface::class);

    $current_route = $this->prophesize(Route::class);
    $current_route->getDefault('_on_relationship')->willReturn(FALSE);

    $current_context_manager->getCurrentRoute()->willReturn(
      $current_route->reveal()
    );

    $resource_config = $this->prophesize(ResourceConfigInterface::class);
    $resource_config->getTypeName()->willReturn('dolor');
    $resource_config->getBundleId()->willReturn('sid');

    $resource_manager = $this->prophesize(ResourceManagerInterface::class);
    $resource_manager->get(Argument::type('string'), Argument::type('string'))
      ->willReturn($resource_config->reveal());
    $current_context_manager->getResourceManager()->willReturn(
      $resource_manager->reveal()
    );

    $this->normalizer = new ConfigEntityNormalizer(
      $link_manager->reveal(),
      $current_context_manager->reveal()
    );

    $normalizers = [new ScalarNormalizer()];
    $serializer = new Serializer($normalizers, []);
    $this->normalizer->setSerializer($serializer);
  }

  /**
   * @covers ::normalize
   * @dataProvider normalizeProvider
   */
  public function testNormalize($input, $expected) {
    $entity = $this->prophesize(ConfigEntityInterface::class);
    $entity->toArray()->willReturn(['amet' => $input]);
    $entity->getCacheContexts()->willReturn([]);
    $entity->getCacheTags()->willReturn([]);
    $entity->getCacheMaxAge()->willReturn(-1);
    $entity->getEntityTypeId()->willReturn('');
    $entity->bundle()->willReturn('');
    $normalized = $this->normalizer->normalize($entity->reveal(), 'api_json', []);
    $first = $normalized->getValues();
    $first = reset($first);
    $this->assertSame($expected, $first->rasterizeValue());
  }

  /**
   * Data provider for the normalize test.
   *
   * @return array
   *   The data for the test method.
   */
  public function normalizeProvider() {
    return [
      ['lorem', 'lorem'],
      [
        ['ipsum' => 'dolor', 'ra' => 'foo'],
        ['ipsum' => 'dolor', 'ra' => 'foo'],
      ],
      [['ipsum' => 'dolor'], 'dolor'],
    ];
  }

}
