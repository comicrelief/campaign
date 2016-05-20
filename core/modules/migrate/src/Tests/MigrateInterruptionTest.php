<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\MigrateInterruptionTest.
 */

namespace Drupal\migrate\Tests;

use Drupal\migrate\Entity\Migration;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\MigrateExecutable;
use Drupal\simpletest\KernelTestBase;

/**
 * Tests interruptions triggered during migrations.
 *
 * @group migrate
 */
class MigrateInterruptionTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['migrate', 'migrate_events_test'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    \Drupal::service('event_dispatcher')->addListener(MigrateEvents::POST_ROW_SAVE,
      array($this, 'postRowSaveEventRecorder'));
  }

  /**
   * Tests migration interruptions.
   */
  public function testMigrateEvents() {
    // Run a simple little migration, which should trigger one of each event
    // other than map_delete.
    $config = [
      'id' => 'sample_data',
      'migration_tags' => ['Interruption test'],
      'source' => [
        'plugin' => 'embedded_data',
        'data_rows' => [
          ['data' => 'dummy value'],
          ['data' => 'dummy value2'],
        ],
        'ids' => [
          'data' => ['type' => 'string'],
        ],
      ],
      'process' => ['value' => 'data'],
      'destination' => ['plugin' => 'dummy'],
    ];

    $migration = Migration::create($config);

    /** @var MigrationInterface $migration */
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    // When the import runs, the first row imported will trigger an
    // interruption.
    $result = $executable->import();

    $this->assertEqual($result, MigrationInterface::RESULT_INCOMPLETE);

    // The status should have been reset to IDLE.
    $this->assertEqual($migration->getStatus(), MigrationInterface::STATUS_IDLE);
  }

  /**
   * Reacts to post-row-save event.
   *
   * @param \Drupal\Migrate\Event\MigratePostRowSaveEvent $event
   *   The migration event.
   * @param string $name
   *   The event name.
   */
  public function postRowSaveEventRecorder(MigratePostRowSaveEvent $event, $name) {
    $event->getMigration()->interruptMigration(MigrationInterface::RESULT_INCOMPLETE);
  }

}
