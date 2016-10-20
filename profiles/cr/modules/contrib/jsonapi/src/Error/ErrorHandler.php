<?php

namespace Drupal\jsonapi\Error;

class ErrorHandler extends ErrorHandlerBase {

  /**
   * {@inheritdoc}
   */
  public static function handle($error_level, $message, $filename, $line, $context) {
    $message = 'Unexpected PHP error: ' . $message;
    _drupal_error_handler($error_level, $message, $filename, $line, $context);
    $types = drupal_error_levels();
    list($severity_msg, $severity_level) = $types[$error_level];
    // Only halt execution if the error is more severe than a warning.
    if ($severity_level < 4) {
      throw new SerializableHttpException(500, sprintf('[%s] %s', $severity_msg, $message), NULL, [], $error_level);
    }
  }

}
