<?php

/**
 * @file
 * Contains \Drupal\focal_point\Plugin\ImageEffect\FocalPointCropImageEffect.
 */

namespace Drupal\focal_point\Plugin\ImageEffect;

use Drupal\focal_point\FocalPoint;
use Drupal\focal_point\FocalPointEffectBase;
use Drupal\Core\Image\ImageInterface;

/**
 * Scales and crops an image resource while keeping its focal point as close to
 * the center of the resulting image as possible.
 *
 * @ImageEffect(
 *   id = "focal_point_crop",
 *   label = @Translation("Focal Point Crop"),
 *   description = @Translation("Crop the image while keeping its focal point as close to the center of the resulting image as possible.")
 * )
 */
class FocalPointCropImageEffect extends FocalPointEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    // Next, attempt to crop the image.
    $focal_point = FocalPoint::getFromURI($image->getSource());
    $crop_data = self::calculateCropData($focal_point, $image->getWidth(), $image->getHeight(), $this->configuration['width'], $this->configuration['height']);
    if (!$image->crop($crop_data['x'], $crop_data['y'], $crop_data['width'], $crop_data['height'])) {
      $this->logger->error('Focal point scale and crop failed while scaling and cropping using the %toolkit toolkit on %path (%mimetype, %dimensions)', array('%toolkit' => $image->getToolkitId(), '%path' => $image->getSource(), '%mimetype' => $image->getMimeType(), '%dimensions' => $image->getWidth() . 'x' . $image->getHeight()));
      return FALSE;
    }

    return TRUE;
  }

}
