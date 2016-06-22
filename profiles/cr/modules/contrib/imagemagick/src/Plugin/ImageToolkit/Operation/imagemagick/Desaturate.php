<?php

namespace Drupal\imagemagick\Plugin\ImageToolkit\Operation\imagemagick;

/**
 * Defines imagemagick Desaturate operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagemagick_desaturate",
 *   toolkit = "imagemagick",
 *   operation = "desaturate",
 *   label = @Translation("Desaturate"),
 *   description = @Translation("Converts an image to grayscale.")
 * )
 */
class Desaturate extends ImagemagickImageToolkitOperationBase {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    // This operation does not use any parameters.
    return array();
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(array $arguments) {
    $this->getToolkit()->addArgument('-colorspace GRAY');
    return TRUE;
  }

}
