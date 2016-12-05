<?php

namespace Drupal\jsonapi\Error;

abstract class ErrorHandlerBase implements ErrorHandlerInterface {

  /**
   * {@inheritdoc}
   */
  public function register() {
    set_error_handler(get_called_class() . '::handle');
  }

  /**
   * {@inheritdoc}
   */
  public function restore() {
    restore_error_handler();
  }

}
