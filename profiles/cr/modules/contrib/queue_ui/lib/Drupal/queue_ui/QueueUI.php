<?php

/**
 * @file
 * Contains \Drupal\queue_ui\QueueUI.
 */

namespace Drupal\queue_ui;

/**
 * Provides factory class for inspecting queue data.
 */
class QueueUI {

  /**
   * Return the QueueUI class object for working with.
   *
   * @param $class
   *  The queue class name to work with.
   *
   * @return mixed
   *  The queue object for a given name, or FALSE if the QueueUI class does not exist for
   *  the specified queue class.
   */
  public static function get($class) {
    if (class_exists($class)) {
      $object = new $class();
      return $object;
    }

    // If class does not exist then QueueUI has not been implemented for this class.
    return FALSE;
  }
}
