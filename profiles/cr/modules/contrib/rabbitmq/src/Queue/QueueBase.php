<?php

/**
 * @file
 * Contains RabbitMQ QueueBase.
 */

namespace Drupal\rabbitmq\Queue;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\rabbitmq\Connection;
use PhpAmqpLib\Channel\AMQPChannel;
use Psr\Log\LoggerInterface;

/**
 * Class QueueBase.
 */
abstract class QueueBase {

  const LOGGER_CHANNEL = 'rabbitmq';

  /**
   * Object that holds a channel to RabbitMQ.
   *
   * @var \PhpAmqpLib\Channel\AMQPChannel
   */
  protected $channel;

  /**
   * The RabbitMQ connection service.
   *
   * @var \Drupal\rabbitmq\Connection
   */
  protected $connection;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $modules;

  /**
   * The name of the queue.
   */
  protected $name;

  /**
   * Constructor.
   *
   * @param string $name
   *   The name of the queue to work with: an arbitrary string.
   * @param \Drupal\rabbitmq\Connection $connection
   *   The RabbitMQ connection service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $modules
   *   The module handler service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger service.
   */
  public function __construct($name, Connection $connection,
    ModuleHandlerInterface $modules, LoggerInterface $logger) {
    $this->name = $name;

    $this->connection = $connection;
    $this->logger = $logger;
    $this->modules = $modules;
  }

  /**
   * Obtain an initialized channel to the queue.
   *
   * @return \PhpAmqpLib\Channel\AMQPChannel
   *   The queue channel.
   */
  public function getChannel() {
    if ($this->channel) {
      return $this->channel;
    }

    $this->channel = $this->connection->getConnection()->channel();

    // Initialize a queue on the channel.
    $this->getQueue($this->channel);
    return $this->channel;
  }

  /**
   * Declare a queue and obtain information about the queue.
   *
   * @param \PhpAmqpLib\Channel\AMQPChannel $channel
   *   The queue channel.
   * @param array $options
   *   Options overriding the queue defaults.
   *
   * @return mixed|null
   *   Not strongly specified by php-amqplib.
   */
  protected function getQueue(AMQPChannel $channel, array $options = []) {
    $queue_options = [
      'passive' => FALSE,
      // Whether the queue is persistent or not. A durable queue is slower but
      // can survive if RabbitMQ fails.
      'durable' => TRUE,
      'exclusive' => FALSE,
      'auto_delete' => FALSE,
      'nowait' => 'FALSE',
      'arguments' => NULL,
      'ticket' => NULL,
    ];

    $queue_info = $this->modules->invokeAll('rabbitmq_queue_info');

    // Allow modules to override queue settings.
    if (isset($queue_info[$this->name])) {
      $queue_options = $queue_info[$this->name];
    }

    $queue_options += $options;
    // The name option cannot be overridden.
    $queue_options['name'] = $this->name;

    return $channel->queue_declare($this->name,
      $queue_options['passive'], $queue_options['durable'],
      $queue_options['exclusive'], $queue_options['auto_delete']);
  }

}
