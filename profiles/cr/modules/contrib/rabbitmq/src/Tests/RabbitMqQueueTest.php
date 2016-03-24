<?php

/**
 * @file
 * Contains RabbitMqQueueTest.
 */

namespace Drupal\rabbitmq\Tests;

use Drupal\rabbitmq\Queue\Queue;
use Drupal\rabbitmq\Queue\QueueFactory;

/**
 * Class RabbitMqQueueTest.
 *
 * @group RabbitMQ
 */
class RabbitMqQueueTest extends RabbitMqTestBase {

  /**
   * The default queue, handled by Beanstalkd.
   *
   * @var \Drupal\beanstalkd\Queue\BeanstalkdQueue
   */
  protected $queue;

  /**
   * The queue factory service.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $queue_factory = $this->container->get('queue');
    $this->queueFactory = $queue_factory;
    $this->queue = $this->queueFactory->get(QueueFactory::DEFAULT_QUEUE_NAME);
    $this->assertTrue($this->queue instanceof Queue, 'Queue API settings point to RabbitMQ');
  }

  /**
   * Test queue registration.
   */
  public function testQueueCycle() {
    list($server, $name,) = $this->initChannel();

    $expected = $this->queue->getName();
    $actual = $name;
    $this->assertEquals($expected, $actual, 'Queue name matches default');

    $data = 'foo';
    $this->queue->createItem($data);

    $this->queue->deleteQueue();
    $actual = $this->queue->numberOfItems();
    $expected = 0;
    $this->assertEquals($expected, $actual, 'Queue no longer contains anything after deletion');

    $this->cleanUp($server, $name);
  }

  /**
   * Test the queue item lifecycle.
   */
  public function testItemCycle() {
    list($server, $name, $count) = $this->initChannel();

    $data = 'foo';
    $this->queue->createItem($data);

    $actual = $this->queue->numberOfItems();
    $expected = $count + 1;
    $this->assertEquals($expected, $actual, 'Creating an item increases the item count.');

    $item = $this->queue->claimItem();
    $this->assertTrue(is_object($item), 'Claiming returns an item');
    $this->assertTrue($item instanceof BeanstalkdQueueItem, 'Claiming returns a correctly typed item');

    $expected = $data;
    $actual = $item->data;
    $this->assertEquals($expected, $actual, 'Item content matches submission.');

    $actual = $this->queue->numberOfItems();
    $expected = $count;
    $this->assertEquals($expected, $actual, 'Claiming an item reduces the item count.');

    $this->queue->releaseItem($item);
    $actual = $this->queue->numberOfItems();
    $expected = $count + 1;
    $this->assertEquals($expected, $actual, 'Releasing an item increases the item count.');

    $this->queue->deleteItem($item);
    $actual = $this->queue->numberOfItems();
    $expected = $count;
    $this->assertEquals($expected, $actual, 'Deleting an item reduces the item count.');

    $this->cleanUp($server, $name);
  }

}
