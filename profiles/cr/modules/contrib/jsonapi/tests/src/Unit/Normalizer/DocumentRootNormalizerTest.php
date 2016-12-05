<?php

namespace Drupal\Tests\jsonapi\Unit\Normalizer;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\jsonapi\Configuration\ResourceConfigInterface;
use Drupal\jsonapi\Normalizer\DocumentRootNormalizer;
use Drupal\jsonapi\LinkManager\LinkManagerInterface;
use Drupal\jsonapi\Context\CurrentContextInterface;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Symfony\Component\Routing\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Class DocumentRootNormalizerTest.
 *
 * @package Drupal\Tests\serialization\Unit\Normalizer
 *
 * @coversDefaultClass \Drupal\jsonapi\Normalizer\DocumentRootNormalizer
 *
 * @group jsonapi
 */
class DocumentRootNormalizerTest extends UnitTestCase {

  /**
   * The normalizer under test.
   *
   * @var \Drupal\jsonapi\Normalizer\DocumentRootNormalizer
   */
  protected $normalizer;

  /**
   * The resource config for the context.
   *
   * @var \Drupal\jsonapi\Configuration\ResourceConfigInterface
   */
  protected $resourceConfig;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $link_manager = $this->prophesize(LinkManagerInterface::class);
    $current_context_manager = $this->prophesize(CurrentContextInterface::class);

    $current_route = $this->prophesize(Route::class);
    $current_route->getDefault('_on_relationship')->willReturn(false);

    $current_context_manager->getCurrentRoute()->willReturn(
      $current_route->reveal()
    );
    $this->resourceConfig = $this->prophesize(ResourceConfigInterface::class);
    $this->resourceConfig->getDeserializationTargetClass()->willReturn(FieldableEntityInterface::class);
    $current_context_manager->getResourceConfig()->willReturn(
      $this->resourceConfig->reveal()
    );

    $this->normalizer = new DocumentRootNormalizer(
      $link_manager->reveal(),
      $current_context_manager->reveal()
    );

    $serializer = $this->prophesize(DenormalizerInterface::class);
    $serializer->willImplement(SerializerInterface::class);
    $serializer->denormalize(
      Argument::type('array'),
      Argument::type('string'),
      Argument::type('string'),
      Argument::type('array')
    )->willReturnArgument(0);

    $this->normalizer->setSerializer($serializer->reveal());
  }

  /**
   * @covers ::denormalize
   * @dataProvider denormalizeProvider
   */
  public function testDenormalize($input, $expected) {
    $this->resourceConfig->getIdKey()->willReturn('id');
    $context = [
      'resource_config' => $this->resourceConfig->reveal(),
    ];
    $denormalized = $this->normalizer->denormalize($input, NULL, 'api_json', $context);
    $this->assertSame($expected, $denormalized);
  }

  /**
   * Data provider for the denormalize test.
   *
   * @return array
   *   The data for the test method.
   */
  public function denormalizeProvider() {
    return [
      [
        [
          'data' => [
            'type' => 'lorem',
            'id' => 42,
            'attributes' => ['title' => 'dummy_title'],
          ],
        ],
        ['title' => 'dummy_title'],
      ],
      [
        [
          'data' => [
            'type' => 'lorem',
            'id' => 42,
            'relationships' => ['field_dummy' => ['data' => ['type' => 'node', 'id' => 1]]],
          ],
        ],
        ['field_dummy' => [1]],
      ],
      [
        [
          'data' => [
            'type' => 'lorem',
            'id' => 42,
            'relationships' => ['field_dummy' => ['data' => [['type' => 'node', 'id' => 1], ['type' => 'node', 'id' => 2]]]],
          ],
        ],
        ['field_dummy' => [1,2]],
      ],
    ];
  }

}
