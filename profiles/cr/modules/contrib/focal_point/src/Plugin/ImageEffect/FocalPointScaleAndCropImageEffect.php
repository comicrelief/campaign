<?php

/**
 * @file
 * Contains \Drupal\focal_point\Plugin\ImageEffect\FocalPointScaleAndCropImageEffect.
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
 *   id = "focal_point_scale_and_crop",
 *   label = @Translation("Focal Point Scale and Crop"),
 *   description = @Translation("Scale and crop will maintain the aspect-ratio of the original image, then crop the larger dimension while keeping its focal point as close to the center of the resulting image as possible.")
 * )
 */
class FocalPointScaleAndCropImageEffect extends FocalPointEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    // First, attempt to resize the image.
    $resize_data = self::calculateResizeData($image->getWidth(), $image->getHeight(), $this->configuration['width'], $this->configuration['height']);
    if (!$image->resize($resize_data['width'], $resize_data['height'])) {
      watchdog('image', 'Focal point scale and crop failed while resizing using the %toolkit toolkit on %path (%mimetype, %dimensions)', array('%toolkit' => $image->getToolkitId(), '%path' => $image->getSource(), '%mimetype' => $image->getMimeType(), '%dimensions' => $image->getWidth() . 'x' . $image->getHeight()), WATCHDOG_ERROR);
      return FALSE;
    }

    // Next, attempt to crop the image.
    $focal_point = FocalPoint::getFromURI($image->getSource());
    $crop_data = self::calculateCropData($focal_point, $image->getWidth(), $image->getHeight(), $this->configuration['width'], $this->configuration['height']);
    if (!$image->crop($crop_data['x'], $crop_data['y'], $crop_data['width'], $crop_data['height'])) {
      watchdog('image', 'Focal point scale and crop failed while scaling and cropping using the %toolkit toolkit on %path (%mimetype, %dimensions)', array('%toolkit' => $image->getToolkitId(), '%path' => $image->getSource(), '%mimetype' => $image->getMimeType(), '%dimensions' => $image->getWidth() . 'x' . $image->getHeight()), WATCHDOG_ERROR);
      return FALSE;
    }

    return TRUE;
  }

}
