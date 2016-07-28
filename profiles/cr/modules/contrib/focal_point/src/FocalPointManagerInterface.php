<?php

namespace Drupal\focal_point;

use Drupal\file\FileInterface;
use Drupal\crop\CropInterface;

/**
 * Defines an interface for focal point manager.
 */
interface FocalPointManagerInterface {

  /**
   * Validates focal point string representation.
   *
   * @param string $focal_point
   *   Focal point as submitted in the form.
   *
   * @return bool
   *   TRUE if valid and FALSE if not.
   */
  public function validateFocalPoint($focal_point);

  /**
   * Converts relative focal point coordinates to absolute.
   *
   * @param float $x
   *   X coordinate of the focal point.
   * @param float $y
   *   Y coordinate of the focal point.
   * @param int $width
   *   Width of the original image.
   * @param int $height
   *   Height of the original image.
   *
   * @return array
   *   Array containing absolute coordinates of the focal point. 'x' and 'y' are
   *   used for array keys and corresponding coordinates as values.
   *
   * @see absoluteToRelative
   */
  public function relativeToAbsolute($x, $y, $width, $height);

  /**
   * Converts absolute focal point coordinates to relative.
   *
   * @param int $x
   *   X coordinate of the focal point.
   * @param int $y
   *   Y coordinate of the focal point.
   * @param int $width
   *   Width of the original image.
   * @param int $height
   *   Height of the original image.
   *
   * @return array
   *   Array containing relative coordinates of the focal point. 'x' and 'y' are
   *   used for array keys and corresponding coordinates as values.
   *
   * @see relativeToAbsolute
   */
  public function absoluteToRelative($x, $y, $width, $height);

  /**
   * Gets a crop entity for the given file.
   *
   * If an existing crop entity is not found then a new one is created.
   *
   * @param \Drupal\file\FileInterface $file
   *   File this focal point applies to.
   * @param string $crop_type
   *   Crop type to be used.
   *
   * @return \Drupal\crop\CropInterface
   *   Created crop entity.
   */
  public function getCropEntity(FileInterface $file, $crop_type);

  /**
   * Converts relative focal point coordinates as a crop entity.
   *
   * @param float $x
   *   X coordinate of the focal point.
   * @param float $y
   *   Y coordinate of the focal point.
   * @param int $width
   *   Width of the original image.
   * @param int $height
   *   Height of the original image.
   * @param \Drupal\crop\CropInterface $crop
   *   Crop entity for the given file.
   *
   * @return \Drupal\crop\CropInterface
   *   Saved crop entity.
   */
  public function saveCropEntity($x, $y, $width, $height, CropInterface $crop);

}
