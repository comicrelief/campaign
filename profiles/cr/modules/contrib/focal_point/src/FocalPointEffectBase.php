<?php

/**
 * @file
 * Contains \Drupal\focal_point\FocalPointEffectBase.
 */

namespace Drupal\focal_point;

use Drupal\image\Plugin\ImageEffect\ResizeImageEffect;

/**
 * Provides a base class for image effects.
 */
abstract class FocalPointEffectBase extends ResizeImageEffect {
  /**
   * Calculate the resize dimensions of an image based on the longest crop
   * dimension so that the aspect ratio is preserved and that there is always
   * enough image available to the crop.
   *
   * @param int $image_width
   * @param int $image_height
   * @param int $crop_width
   * @param int $crop_height
   *
   * @return array
   */
  public static function calculateResizeData($image_width, $image_height, $crop_width, $crop_height) {
    $resize_data = array();

    if ($crop_width > $crop_height) {
      $resize_data['width'] = (int) $crop_width;
      $resize_data['height'] = (int) ($crop_width * $image_height / $image_width);

      // Ensure there is enough area to crop.
      if ($resize_data['height'] < $crop_height) {
        $resize_data['width'] = (int) ($crop_height * $resize_data['width'] / $resize_data['height']);
        $resize_data['height'] = (int) $crop_height;
      }
    }
    else {
      $resize_data['width'] = (int) ($crop_height * $image_width / $image_height);
      $resize_data['height'] = (int) $crop_height;

      // Ensure there is enough area to crop.
      if ($resize_data['width'] < $crop_width) {
        $resize_data['height'] = (int) ($crop_width * $resize_data['height'] / $resize_data['width']);
        $resize_data['width'] = (int) $crop_width;
      }
    }

    return $resize_data;
  }

  /**
   * Compile the necessary data for the image crop effect.
   *
   * @param string $focal_point
   * @param int $image_width
   * @param int $image_height
   * @param int $crop_width
   * @param int $crop_height
   *
   * @return array|bool
   *   An array containing the following keys:
   *    - width
   *    - height
   *    - x
   *    - y
   */
  public static function calculateCropData($focal_point, $image_width, $image_height, $crop_width, $crop_height) {
    $crop_data = array();
    $parsed_focal_point = FocalPoint::parse($focal_point);

    // Get the pixel location of the focal point for the current image taking
    // the image boundaries into account.
    $crop_data['width'] = (int) $crop_width;
    $crop_data['height'] = (int) $crop_height;
    $crop_data['x'] = self::calculateAnchor($image_width, $crop_width, $parsed_focal_point['x-offset']);
    $crop_data['y'] = self::calculateAnchor($image_height, $crop_height, $parsed_focal_point['y-offset']);

    return $crop_data;
  }

  /**
   * Calculate the anchor offset for the given dimension.
   *
   * @param int $image_size
   *   The dimension of the full-sized image.
   * @param int $crop_size
   *   The dimension of the crop.
   * @param int $focal_point_offset
   *   The corresponding focal point percentage value for the given dimension.
   *
   * @return int
   */
  public static function calculateAnchor($image_size, $crop_size, $focal_point_offset) {
    $focal_point_pixel = (int) $focal_point_offset * $image_size / 100;

    // If the crop size is larger than the image size, use the image size to avoid
    // stretching. This will cause the excess space to be filled with black.
    $crop_size = min($image_size, $crop_size);

    // Define the anchor as half the crop width to the left.
    $offset = (int) ($focal_point_pixel - (.5 * $crop_size));
    // Ensure the anchor doesn't fall off the left edge of the image.
    $offset = max($offset, 0);
    // Ensure the anchor doesn't fall off the right side of the image.
    if ($offset + $crop_size > $image_size) {
      $offset = $image_size - $crop_size;
    }

    return $offset;
  }

}
