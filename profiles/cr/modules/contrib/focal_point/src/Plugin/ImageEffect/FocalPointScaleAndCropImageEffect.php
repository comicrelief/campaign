<?php

/**
 * @file
 * Contains \Drupal\focal_point\Plugin\ImageEffect\FocalPointScaleAndCropImageEffect.
 */

namespace Drupal\focal_point\Plugin\ImageEffect;

use Drupal\crop\Entity\Crop;
use Drupal\focal_point\FocalPointEffectBase;
use Drupal\Core\Image\ImageInterface;

/**
 * Scales and crops image while keeping its focal point close to centered.
 *
 * @ImageEffect(
 *   id = "focal_point_scale_and_crop",
 *   label = @Translation("Focal Point Scale and Crop"),
 *   description = @Translation("Scales and crops image while keeping its focal point close to centered.")
 * )
 */
class FocalPointScaleAndCropImageEffect extends FocalPointEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    // Hold on to the original width and height. We'll need it later.
    $image_size = [
      'width' => $image->getWidth(),
      'height' => $image->getHeight(),
    ];

    // First, attempt to resize the image.
    $resize_data = self::calculateResizeData($image_size['width'], $image_size['height'], $this->configuration['width'], $this->configuration['height']);
    if (!$image->resize($resize_data['width'], $resize_data['height'])) {
      $this->logger->error(
        'Focal point scale and crop failed while resizing using the %toolkit toolkit on %path (%mimetype, %dimensions)',
        [
          '%toolkit' => $image->getToolkitId(),
          '%path' => $image->getSource(),
          '%mimetype' => $image->getMimeType(),
          '%dimensions' => $image->getWidth() . 'x' . $image->getHeight(),
        ]
      );
      return FALSE;
    }

    // Next, attempt to crop the image.
    return $this->applyCrop($image, $image_size);
  }

}
