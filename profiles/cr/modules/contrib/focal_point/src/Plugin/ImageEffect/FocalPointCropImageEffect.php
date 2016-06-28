<?php

/**
 * @file
 * Contains \Drupal\focal_point\Plugin\ImageEffect\FocalPointCropImageEffect.
 */

namespace Drupal\focal_point\Plugin\ImageEffect;

use Drupal\focal_point\FocalPointEffectBase;
use Drupal\Core\Image\ImageInterface;

/**
 * Crops image while keeping its focal point as close to centered as possible.
 *
 * @ImageEffect(
 *   id = "focal_point_crop",
 *   label = @Translation("Focal Point Crop"),
 *   description = @Translation("Crops image while keeping its focal point as close to centered as possible.")
 * )
 */
class FocalPointCropImageEffect extends FocalPointEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    return $this->applyCrop($image);
  }

}
