<?php

namespace Drupal\Tests\Core\Database;

use Drupal\Core\Database\StatementEmpty;
use Drupal\Core\Database\StatementInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the empty pseudo-statement class.
 *
 * @group Database
 */
class EmptyStatementTest extends UnitTestCase {
  /**
   * Tests that the empty result set behaves as empty.
   */
  function testEmpty() {
    $result = new StatementEmpty();

    $this->assertTrue($result instanceof StatementInterface, 'Class implements expected interface');
    $this->assertNull($result->fetchObject(), 'Null result returned.');
  }

  /**
   * Tests that the empty result set iterates safely.
   */
  function testEmptyIteration() {
    $result = new StatementEmpty();
    $this->assertSame(0, iterator_count($result), 'Empty result set should not iterate.');
  }

  /**
   * Tests that the empty result set mass-fetches in an expected way.
   */
  function testEmptyFetchAll() {
    $result = new StatementEmpty();

    $this->assertEquals($result->fetchAll(), array(), 'Empty array returned from empty result set.');
  }
}
