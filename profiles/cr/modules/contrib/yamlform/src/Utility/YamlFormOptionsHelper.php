<?php

namespace Drupal\yamlform\Utility;

/**
 * Helper class form options based methods.
 */
class YamlFormOptionsHelper {

  /**
   * Determine if the options has a specified value..
   *
   * @param string $value
   *   An value to look for in the options.
   * @param array $options
   *   An associative array of options.
   *
   * @return bool
   *   TRUE if the options has a specified value.
   */
  public static function hasOption($value, array $options) {
    foreach ($options as $option_value => $option_text) {
      if (is_array($option_text)) {
        if ($has_value = self::hasOption($value, $option_text)) {
          return $has_value;
        }
      }
      elseif ($value == $option_value) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Replace associative array of option values with option text.
   *
   * @param array $values
   *   The option value.
   * @param array $options
   *   An associative array of options.
   *
   * @return array
   *   An associative array of option values with option text.
   */
  public static function getOptionsText(array $values, array $options) {
    foreach ($values as &$value) {
      $value = self::getOptionText($value, $options);
    }
    return $values;
  }

  /**
   * Get the text string for an option value.
   *
   * @param string $value
   *   The option value.
   * @param array $options
   *   An associative array of options.
   *
   * @return string
   *   The option text if found or the option value.
   */
  public static function getOptionText($value, array $options) {
    foreach ($options as $option_value => $option_text) {
      if (is_array($option_text)) {
        if ($text = self::getOptionText($value, $option_text)) {
          return $text;
        }
      }
      elseif ($value == $option_value) {
        return $option_text;
      }
    }
    return $value;
  }

  /**
   * Build an associative array containing a range of options.
   *
   * @param int|string $min
   *   First value of the sequence.
   * @param int $max
   *   The sequence is ended upon reaching the end value.
   * @param int $step
   *   Increments between the range. Default value is 1.
   * @param int $pad_length
   *   Number of character to be prepended to the range.
   * @param string $pad_str
   *   The character to default the string.
   *
   * @return array
   *   An associative array containing a range of options.
   */
  public static function range($min = 1, $max = 100, $step = 1, $pad_length = NULL, $pad_str = '0') {
    // Create range.
    $range = range($min, $max, $step);

    // Pad left on range.
    if ($pad_length) {
      $range = array_map(function ($item) use ($pad_length, $pad_str) {
        return str_pad($item, $pad_length, $pad_str, STR_PAD_LEFT);
      }, $range);
    }

    // Return associative array of range options.
    return array_combine($range, $range);
  }

}
