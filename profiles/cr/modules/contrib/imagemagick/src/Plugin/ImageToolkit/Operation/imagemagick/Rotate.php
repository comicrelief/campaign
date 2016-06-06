<?php

namespace Drupal\imagemagick\Plugin\ImageToolkit\Operation\imagemagick;

use Drupal\Component\Utility\Color;
use Drupal\Component\Utility\Rectangle;

/**
 * Defines imagemagick Rotate operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagemagick_rotate",
 *   toolkit = "imagemagick",
 *   operation = "rotate",
 *   label = @Translation("Rotate"),
 *   description = @Translation("Rotates an image by the given number of degrees.")
 * )
 */
class Rotate extends ImagemagickImageToolkitOperationBase {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array(
      'degrees' => array(
        'description' => 'The number of (clockwise) degrees to rotate the image',
      ),
      'background' => array(
        'description' => "A string specifying the hexadecimal color code to use as background for the uncovered area of the image after the rotation. E.g. '#000000' for black, '#ff00ff' for magenta, and '#ffffff' for white. For images that support transparency, this will default to transparent white",
        'required' => FALSE,
        'default' => NULL,
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function validateArguments(array $arguments) {
    // Validate or set background color argument.
    if (!empty($arguments['background'])) {
      // Validate the background color.
      if (!Color::validateHex($arguments['background'])) {
        throw new \InvalidArgumentException("Invalid color '{$arguments['background']}' specified for the 'rotate' operation.");
      }
    }
    else {
      // Background color is not specified: use transparent.
      $arguments['background'] = 'transparent';
    }
    return $arguments;
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(array $arguments) {
    // Rotate.
    $this->getToolkit()
      ->addArgument('-background ' . $this->getToolkit()->escapeShellArg($arguments['background']))
      ->addArgument('-rotate ' . $arguments['degrees'])
      ->addArgument('+repage');

    // Adjust width and height.
    $box = new Rectangle($this->getToolkit()->getWidth(), $this->getToolkit()->getHeight());
    $box = $box->rotate((float) $arguments['degrees']);
    return $this->getToolkit()->apply('resize', ['width' => $box->getBoundingWidth(), 'height' => $box->getBoundingHeight()]);
  }
}
