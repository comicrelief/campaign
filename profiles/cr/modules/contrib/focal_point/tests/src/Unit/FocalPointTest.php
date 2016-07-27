<?php

/**
 * @file
 * Contains \Drupal\Tests\focal_point\Unit\FocalPointTest.
 */

namespace Drupal\Tests\focal_point\Unit;

use Drupal\crop\CropStorageInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\focal_point\FocalPointManager;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\focal_point\FocalPointManager
 *
 * @group Focal Point
 */
class FocalPointTest extends UnitTestCase {

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

    $container = $this->prophesize(ContainerInterface::class);
    $container->get('entity_type.manager')->willReturn($entity_type_manager);

    \Drupal::setContainer($container->reveal());

    $this->focalPointManager = new FocalPointManager(\Drupal::service('entity_type.manager'));
  }

  /**
   * @covers ::validateFocalPoint
   *
   * @dataProvider providerValidateFocalPoint
   */
  public function testFocalPointValidate($value, $expected) {
    $this->assertEquals($expected, $this->focalPointManager->validateFocalPoint($value));
  }

  /**
   * Data provider for testFocalPoint().
   */
  public function providerValidateFocalPoint() {
    $data = [];
    $data['default_focal_point_position'] = ['50,50', TRUE];
    $data['basic_focal_point_position_1'] = ['75,25', TRUE];
    $data['basic_focal_point_position_2'] = ['3,50', TRUE];
    $data['basic_focal_point_position_3'] = ['83,6', TRUE];
    $data['basic_focal_point_position_4'] = ['2,9', TRUE];
    $data['extreme_focal_point_position_top_right'] = ['100,0', TRUE];
    $data['extreme_focal_point_position_top_left'] = ['0,0', TRUE];
    $data['extreme_focal_point_position_bottom_right'] = ['100,100', TRUE];
    $data['extreme_focal_point_position_bottom_left'] = ['0,100', TRUE];
    $data['invalid_focal_point_position_negative_x'] = ['-20,50', FALSE];
    $data['invalid_focal_point_position_negative_y'] = ['18,-3', FALSE];
    $data['invalid_focal_point_position_out_of_bounds_x'] = ['101,33', FALSE];
    $data['invalid_focal_point_position_out_of_bounds_y'] = ['44,101', FALSE];
    $data['invalid_focal_point_position_out_of_bounds_xy'] = ['313,512', FALSE];
    $data['invalid_focal_point_position_empty'] = ['', FALSE];
    $data['invalid_focal_point_position_incorrect_format_1'] = ['invalid', FALSE];
    $data['invalid_focal_point_position_incorrect_format_2'] = ['invalid,invalid', FALSE];
    $data['invalid_focal_point_position_incorrect_format_3'] = ['23,invalid', FALSE];

    return $data;
  }

  /**
   * @covers ::relativeToAbsolute
   *
   * @dataProvider providerCoordinates
   */
  public function testRelativeToAbsolute($relative, $size, $absolute) {
    $this->assertEquals(
      $absolute,
      $this->focalPointManager
        ->relativeToAbsolute($relative['x'], $relative['y'], $size['width'], $size['height'])
    );
  }

  /**
   * @covers ::absoluteToRelative
   *
   * @dataProvider providerCoordinates
   */
  public function testAbsoluteToRelative($relative, $size, $absolute) {
    $this->assertEquals(
      $relative,
      $this->focalPointManager
        ->absoluteToRelative($absolute['x'], $absolute['y'], $size['width'], $size['height'])
    );
  }

  /**
   * Data provider for testRelativeToAbsolute() and absoluteToRelative().
   */
  public function providerCoordinates() {
    $data = [];
    $data['top_left'] = [
      ['x' => 0, 'y' => 0],
      ['width' => 1000, 'height' => 2000],
      ['x' => 0, 'y' => 0],
    ];
    $data['basic_case_1'] = [
      ['x' => 25, 'y' => 50],
      ['width' => 1000, 'height' => 2000],
      ['x' => 250, 'y' => 1000]
    ];
    $data['basic_case_2'] = [
      ['x' => 50, 'y' => 25],
      ['width' => 1000, 'height' => 2000],
      ['x' => 500, 'y' => 500],
    ];
    $data['basic_case_3'] = [
      ['x' => 50, 'y' => 50],
      ['width' => 1000, 'height' => 2000],
      ['x' => 500, 'y' => 1000],
    ];
    $data['basic_case_4'] = [
      ['x' => 75, 'y' => 50],
      ['width' => 1000, 'height' => 2000],
      ['x' => 750, 'y' => 1000],
    ];
    $data['basic_case_5'] = [
      ['x' => 100, 'y' => 75],
      ['width' => 1000, 'height' => 2000],
      ['x' => 1000, 'y' => 1500],
    ];
    $data['bottom_right'] = [
      ['x' => 100, 'y' => 100],
      ['width' => 1000, 'height' => 2000],
      ['x' => 1000, 'y' => 2000],
    ];

    return $data;
  }

}
