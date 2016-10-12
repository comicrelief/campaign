<?php

namespace Drupal\yamlform\Utility;

/**
 * Helper class form date helper methods.
 */
class YamlFormDateHelper {

  /**
   * Wrapper for DateFormatter that return an empty string for empty timestamps.
   *
   * @param int $timestamp
   *   A UNIX timestamp to format.
   * @param string $type
   *   (optional) The data format to use.
   * @param string $format
   *   (optional) If $type is 'custom', a PHP date format string suitable for
   *   element to date(). Use a backslash to escape ordinary text, so it does not
   *   get interpreted as date format characters.
   * @param string|null $timezone
   *   (optional) Time zone identifier, as described at
   *   http://php.net/manual/timezones.php Defaults to the time zone used to
   *   display the page.
   * @param string|null $langcode
   *   (optional) Language code to translate to. NULL (default) means to use
   *   the user interface language for the page.
   *
   * @return string
   *   A translated date string in the requested format.  An empty string
   *   will be returned for empty timestamps.
   *
   * @see \Drupal\Core\Datetime\DateFormatterInterface::format
   */
  public static function format($timestamp, $type = 'fallback', $format = '', $timezone = NULL, $langcode = NULL) {
    /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
    $date_formatter = \Drupal::service('date.formatter');
    return $timestamp ? $date_formatter->format($timestamp, $type) : '';
  }

}
