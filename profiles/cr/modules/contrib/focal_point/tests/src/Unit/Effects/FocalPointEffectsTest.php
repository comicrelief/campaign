<?php

/**
 * @file
 * Contains \Drupal\focal_point\Tests\FocalPointEffectsTest.
 */

namespace Drupal\Tests\focal_point\Unit\Effects;

use Drupal\Tests\UnitTestCase;
use Drupal\focal_point\FocalPointEffectBase;

/**
 * Tests the Focal Point image effects.
 *
 * @group Focal Point
 * @group Drupal
 *
 * @see \Drupal\focal_point\FocalPointEffectBase
 */
class FocalPointEffectsTest extends UnitTestCase {

  /**
   * @dataProvider calculateResizeDataProvider
   */
  public function testCalculateResizeData($image_width, $image_height, $crop_width, $crop_height, $expected) {
    $this->assertSame($expected, FocalPointEffectBase::calculateResizeData($image_width, $image_height, $crop_width, $crop_height));
  }

  /**
   * Data provider for testCalculateResizeData().
   *
   * @see FocalPointEffectsTest::testCalculateResizeData()
   */
  public function calculateResizeDataProvider() {
    return array(
      array(640, 480, 300, 100, array('width' => 300, 'height' => 225)), // Horizontal image with horizontal crop.
      array(640, 480, 100, 300, array('width' => 400, 'height' => 300)), // Horizontal image with vertical crop.
      array(480, 640, 300, 100, array('width' => 300, 'height' => 400)), // Vertical image with horizontal crop.
      array(480, 640, 100, 300, array('width' => 225, 'height' => 300)), // Vertical image with vertical crop.
      array(640, 480, 3000, 1000, array('width' => 3000, 'height' => 2250)), // Horizontal image with too large crop.
      array(1920, 1080, 400, 300, array('width' => 533, 'height' => 300)), // Image would be too narrow to crop after resize.
      array(200, 400, 1000, 1000, array('width' => 1000, 'height' => 2000)), // Image would be too short to crop after resize.
    );
  }

  /**
   * @dataProvider calculateCropDataProvider
   */
  public function testCalculateCropData($focal_point, $image_width, $image_height, $crop_width, $crop_height, $expected) {
    $this->assertSame($expected, FocalPointEffectBase::calculateCropData($focal_point, $image_width, $image_height, $crop_width, $crop_height));
  }

  /**
   * Data provider for testCalculateCropData().
   *
   * @see FocalPointEffectsTest::testCalculateCropData()
   */
  public function calculateCropDataProvider() {
    return array(
      array('50,50', 640, 480, 300, 100, array('width' => 300, 'height' => 100, 'x' => 170, 'y' => 190)),
      array('50,50', 640, 480, 100, 300, array('width' => 100, 'height' => 300, 'x' => 270, 'y' => 90)),
      array('50,50', 480, 640, 300, 100, array('width' => 300, 'height' => 100, 'x' => 90, 'y' => 270)),
      array('50,50', 480, 640, 100, 300, array('width' => 100, 'height' => 300, 'x' => 190, 'y' => 170)),
      array('50,50', 1920, 1080, 400, 300, array('width' => 400, 'height' => 300, 'x' => 760, 'y' => 390)),

      array('invalid', 640, 480, 300, 100, array('width' => 300, 'height' => 100, 'x' => 170, 'y' => 190)),
      array('invalid', 640, 480, 100, 300, array('width' => 100, 'height' => 300, 'x' => 270, 'y' => 90)),
      array('invalid', 480, 640, 300, 100, array('width' => 300, 'height' => 100, 'x' => 90, 'y' => 270)),
      array('invalid', 480, 640, 100, 300, array('width' => 100, 'height' => 300, 'x' => 190, 'y' => 170)),
      array('invalid', 1920, 1080, 400, 300, array('width' => 400, 'height' => 300, 'x' => 760, 'y' => 390)),

      array('75,25', 640, 480, 300, 100, array('width' => 300, 'height' => 100, 'x' => 330, 'y' => 70)),
      array('75,25', 640, 480, 100, 300, array('width' => 100, 'height' => 300, 'x' => 430, 'y' => 0)),
      array('75,25', 480, 640, 300, 100, array('width' => 300, 'height' => 100, 'x' => 180, 'y' => 110)),
      array('75,25', 480, 640, 100, 300, array('width' => 100, 'height' => 300, 'x' => 310, 'y' => 10)),
      array('75,25', 1920, 1080, 400, 300, array('width' => 400, 'height' => 300, 'x' => 1240, 'y' => 120)),
    );
  }

  /**
   * @dataProvider calculateAnchorProvider
   */
  public function testCalculateAnchor($image_size, $crop_size, $focal_point_offset, $expected) {
    $this->assertSame($expected, FocalPointEffectBase::calculateAnchor($image_size, $crop_size, $focal_point_offset));
  }

  /**
   * Data provider for testCalculateAnchor().
   *
   * @see FocalPointEffectsTest::testCalculateAnchor()
   */
  public function calculateAnchorProvider() {
    return array(
      array(640, 300, 50, 170),
      array(640, 300, 80, 340),
      array(640, 300, 10, 0),
      array(640, 640, 640, 0),
      array(640, 800, 50, 0),
    );
  }

}
