<?php

/**
 * @file
 * Contains \Drupal\yamlform\Utility\YamlFormArrayHelper.
 */

namespace Drupal\yamlform\Utility;

/**
 * Provides helper to operate on arrays.
 */
class YamlFormArrayHelper {

  /**
   * Implode an array with commas separating the elements and with an "and" before the last element.
   *
   * @param array $array
   *   The array to be convert to a string.
   * @param string $conjunction
   *   (optional) The word, which should be 'and' or 'or' used to join the
   *   values of the array. Defaults to 'and'.
   *
   * @return string
   *   The array converted to a string.
   */
  public static function toString(array $array, $conjunction = 'and') {
    switch (count($array)) {
      case 0:
        return '';

      case 1:
        return reset($array);

      case 2:
        return implode(' ' . $conjunction . ' ', $array);

      default:
        $last = array_pop($array);
        return implode(', ', $array) . ', ' . $conjunction . ' ' . $last;
    }
  }

}
