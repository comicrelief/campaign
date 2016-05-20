<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\MigrateDumpAlterInterface.
 */

namespace Drupal\migrate\Tests;

use Drupal\simpletest\TestBase;

/**
 * Allows tests to alter dumps after they have loaded.
 *
 * @see \Drupal\migrate_drupal\Tests\d6\MigrateFileTest
 */
interface MigrateDumpAlterInterface {

  /**
   * Allows tests to alter dumps after they have loaded.
   *
   * @param \Drupal\simpletest\TestBase $test
   *   The test that is being run.
   */
  public static function migrateDumpAlter(TestBase $test);

}
