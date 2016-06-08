<?php

/**
 * @file
 * Contains \Drupal\purge\Tests\KernelTestBase.
 */

namespace Drupal\purge\Tests;

use Drupal\purge\Tests\TestTrait;
use Drupal\simpletest\KernelTestBase as RealKernelTestBase;

/**
 * Thin and generic KTB for purge tests.
 *
 * @see \Drupal\simpletest\KernelTestBase
 * @see \Drupal\purge\Tests\TestTrait
 */
abstract class KernelTestBase extends RealKernelTestBase {
  use TestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['purge'];

  /**
   * Set up the test object.
   *
   * @param bool $switch_to_memory_queue
   *   Whether to switch the default queue to the memory backend or not.
   *
   */
  function setUp($switch_to_memory_queue = TRUE) {
    parent::setUp();
    $this->installConfig(['purge']);

    // The default 'database' queue backend gives issues, switch to 'memory'.
    if ($switch_to_memory_queue) {
      $this->setMemoryQueue();
    }
  }

}
