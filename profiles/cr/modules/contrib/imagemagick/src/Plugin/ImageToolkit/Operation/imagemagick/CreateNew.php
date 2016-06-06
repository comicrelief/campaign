<?php

namespace Drupal\imagemagick\Plugin\ImageToolkit\Operation\imagemagick;

use Drupal\Component\Utility\Color;

/**
 * Defines imagemagick CreateNew operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagemagick_create_new",
 *   toolkit = "imagemagick",
 *   operation = "create_new",
 *   label = @Translation("Set a new image"),
 *   description = @Translation("Creates a new transparent resource and sets it for the image.")
 * )
 */
class CreateNew extends ImagemagickImageToolkitOperationBase {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array(
      'width' => array(
        'description' => 'The width of the image, in pixels',
      ),
      'height' => array(
        'description' => 'The height of the image, in pixels',
      ),
      'extension' => array(
        'description' => 'The extension of the image file (e.g. png, gif, etc.)',
        'required' => FALSE,
        'default' => 'png',
      ),
      'transparent_color' => array(
        'description' => 'The RGB hex color for GIF transparency',
        'required' => FALSE,
        'default' => '#ffffff',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function validateArguments(array $arguments) {
    // Assure extension is supported.
    if (!in_array($arguments['extension'], $this->getToolkit()->getSupportedExtensions())) {
      throw new \InvalidArgumentException("Invalid extension ('{$arguments['extension']}') specified for the image 'create_new' operation");
    }

    // Assure integers for width and height.
    $arguments['width'] = (int) round($arguments['width']);
    $arguments['height'] = (int) round($arguments['height']);

    // Fail when width or height are 0 or negative.
    if ($arguments['width'] <= 0) {
      throw new \InvalidArgumentException("Invalid width ('{$arguments['width']}') specified for the image 'create_new' operation");
    }
    if ($arguments['height'] <= 0) {
      throw new \InvalidArgumentException("Invalid height ({$arguments['height']}) specified for the image 'create_new' operation");
    }

    // Assure transparent color is a valid hex string.
    if ($arguments['transparent_color'] && !Color::validateHex($arguments['transparent_color'])) {
      throw new \InvalidArgumentException("Invalid transparent color ({$arguments['transparent_color']}) specified for the image 'create_new' operation");
    }

    return $arguments;
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(array $arguments) {
    $this->getToolkit()
      ->resetArguments()
      ->setSourceLocalPath('')
      ->setSourceFormatFromExtension($arguments['extension'])
      ->setWidth($arguments['width'])
      ->setHeight($arguments['height'])
      ->setExifOrientation(NULL)
      ->setFrames(NULL)
      ->addArgument('-size ' . $arguments['width'] . 'x' . $arguments['height'] . ' xc:transparent');
    if ($arguments['extension'] == 'gif') {
      $this->getToolkit()->addArgument('-transparent-color ' . $this->getToolkit()->escapeShellArg($arguments['transparent_color']));
    }
    return TRUE;
  }

}
