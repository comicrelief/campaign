<?php

namespace Drupal\imagemagick\Plugin\ImageToolkit\Operation\imagemagick;

use Drupal\Core\ImageToolkit\ImageToolkitOperationBase;

abstract class ImagemagickImageToolkitOperationBase extends ImageToolkitOperationBase {

  /**
   * The correctly typed image toolkit for imagemagick operations.
   *
   * @return \Drupal\imagemagick\Plugin\ImageToolkit\ImagemagickToolkit
   */
  protected function getToolkit() {
    return parent::getToolkit();
  }

}
