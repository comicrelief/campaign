<?php

namespace Drupal\imagemagick\Plugin\ImageToolkit\Operation\imagemagick;

/**
 * Defines imagemagick Convert operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagemagick_convert",
 *   toolkit = "imagemagick",
 *   operation = "convert",
 *   label = @Translation("Convert"),
 *   description = @Translation("Instructs the toolkit to save the image with a specified format.")
 * )
 */
class Convert extends ImagemagickImageToolkitOperationBase {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array(
      'extension' => array(
        'description' => 'The new extension of the converted image',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function validateArguments(array $arguments) {
    if (!in_array($arguments['extension'], $this->getToolkit()->getSupportedExtensions())) {
      throw new \InvalidArgumentException("Invalid extension ({$arguments['extension']}) specified for the image 'convert' operation");
    }
    return $arguments;
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(array $arguments) {
    // When source image is multi-frame, convert only the first frame.
    if ($this->getToolkit()->getFrames()) {
      $path = $this->getToolkit()->getSourceLocalPath();
      if (strripos($path, '[0]', -3) === FALSE) {
        $this->getToolkit()->setSourceLocalPath($path . '[0]');
      }
    }
    $this->getToolkit()
      ->setFrames(NULL)
      ->setDestinationFormatFromExtension($arguments['extension']);
    return TRUE;
  }

}
