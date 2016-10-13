<?php

namespace Drupal\yamlform\Utility;

use Drupal\Component\Serialization\Json;

/**
 * Helper class for dialog methods.
 */
class YamlFormDialogHelper {

  /**
   * Get modal dialog attributes.
   *
   * @param int $width
   *   Width of the modal dialog.
   * @param array $class
   *   Additional class names to be included in the dialog's attributes.
   *
   * @return array
   *   Modal dialog attributes.
   */
  static public function getModalDialogAttributes($width = 800, array $class = []) {
    if (\Drupal::config('yamlform.settings')->get('ui.dialog_disabled')) {
      return $class ? ['class' => $class] : [];
    }
    else {
      $class[] = 'use-ajax';
      return [
        'class' => $class,
        'data-dialog-type' => 'modal',
        'data-dialog-options' => Json::encode([
          'width' => $width,
        ]),
      ];
    }
  }

}
