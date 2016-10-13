<?php

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

  /**
   * Determine if an array is an associative array.
   *
   * @param array $array
   *   An array.
   *
   * @return bool
   *   TRUE if array is an associative array.
   *
   * @see http://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
   */
  public static function isAssociative(array $array) {
    return array_keys($array) !== range(0, count($array) - 1);
  }

  /**
   * Determine if an array is a sequential array.
   *
   * @param array $array
   *   An array.
   *
   * @return bool
   *   TRUE if array is a sequential array.
   */
  public static function isSequential(array $array) {
    return !self::isAssociative($array);
  }

}
