<?php

namespace Drupal\jsonapi\Error;

interface ErrorHandlerInterface {

  /**
   * Register the handler.
   */
  public function register();

  /**
   * Go back to normal and restore the previous error handler.
   */
  public function restore();

  /**
   * Handle the PHP error with custom business logic.
   *
   * @param $error_level
   *   The level of the error raised.
   * @param $message
   *   The error message.
   * @param $filename
   *   The filename that the error was raised in.
   * @param $line
   *   The line number the error was raised at.
   * @param $context
   *   An array that points to the active symbol table at the point the error
   *   occurred.
   */
  public static function handle($error_level, $message, $filename, $line, $context);

}
