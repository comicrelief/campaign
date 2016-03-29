<?php

/**
 * @file
 * Contains RabbitMqTestBase.
 */

namespace Drupal\rabbitmq\Tests;

use Doctrine\Common\Util\Debug;
use Drupal\rabbitmq\Queue\QueueFactory;
use Drupal\rabbitmq\Connection;
use Drupal\KernelTests\KernelTestBase;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;

/**
 * Class BeanstalkdTestBase is a base class for Beanstalkd tests.
 */
abstract class RabbitMqTestBase extends KernelTestBase {

  public static $modules = ['rabbitmq'];

  /**
   * Server factory.
   *
   * @var \Drupal\rabbitmq\Connection
   */
  protected $connectionFactory;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    // Override the database queue to ensure all requests to it come to us.
    $this->container->setAlias('queue.database', QueueFactory::DEFAULT_QUEUE_NAME);
    $this->connectionFactory = $this->container->get('rabbitmq.connection.factory');
  }

  /**
   * Initialize a server and free channel.
   *
   * @return \AMQPChannel
   *   A channel to the default queue.
   */
  protected function initChannel() {
    $connection = $this->connectionFactory->getConnection();
    $this->assertTrue($connection instanceof AMQPStreamConnection, 'Default connections is an AMQP Connection');
    $channel = $connection->channel();
    $this->assertTrue($channel instanceof AMQPChannel, 'Default connection provides channels');
    $name = QueueFactory::DEFAULT_QUEUE_NAME;
    $passive = FALSE;
    $durable = TRUE;
    $exclusive = FALSE;
    $auto_delete = FALSE;

    list($actual_name,,) = $channel->queue_declare($name, $passive, $durable, $exclusive, $auto_delete);
    $this->assertEquals($name, $actual_name, 'Queue declaration succeeded');

    return $channel;
  }

  /**
   * Clean up after a test.
   *
   * @param \PhpAmqpLib\Channel\AMQPChannel $channel
   *   The channel to clean up.
   */
  protected function cleanUp(AMQPChannel $channel) {
    $connection = $channel->getConnection();
    $channel->close();
    $connection->close();
  }

}
