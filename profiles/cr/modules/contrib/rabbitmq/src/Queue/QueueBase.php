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
   * The queue options.
   */
  protected $options;

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
    $this->options = ['name' => $name];

    // Check our active storage to find the the queue config
    $config = \Drupal::config('rabbitmq.config');
    $queues = $config->get('queues');
    if ($queues && isset($queues[$name])) {
      $this->options += $queues[$name];
    }

    $this->name = $name;
    $this->connection = $connection;
    $this->logger = $logger;
    $this->modules = $modules;

    // Declare any exchanges required if configured
    $exchanges = $config->get('exchanges');
    if ($exchanges) {
      foreach ($exchanges as $name => $exchange) {
        $this->getChannel()->exchange_declare(
          $name, 
          isset($exchange['type']) ? $exchange['type'] : 'direct',
          isset($exchange['passive']) ? $exchange['passive'] : FALSE,
          isset($exchange['durable']) ? $exchange['durable'] : TRUE,
          isset($exchange['auto_delete']) ? $exchange['auto_delete'] : FALSE,
          isset($exchange['internal']) ? $exchange['internal'] : FALSE,
          isset($exchange['nowait']) ? $exchange['nowait'] : FALSE
        );            
      }
    }
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
    // Declare the queue
    $queue = $channel->queue_declare(
      $this->name,
      isset($this->options['passive']) ? $this->options['passive'] : false,
      isset($this->options['durable']) ? $this->options['durable'] : true,
      isset($this->options['exclusive']) ? $this->options['exclusive'] : false,
      isset($this->options['auto_delete']) ? $this->options['auto_delete'] : true,
      isset($this->options['nowait']) ? $this->options['nowait'] : false,
      isset($this->options['arguments']) ? $this->options['arguments'] : null,
      isset($this->options['ticket']) ? $this->options['ticket'] : null
    );

    // Bind the queue to an exchange if defined
    if ($queue && !empty($this->options['routing_keys'])) {
      foreach ($this->options['routing_keys'] as $routing_key) {
        list($exchange, $key) = explode('.', $routing_key);
        $this->channel->queue_bind($this->name, $exchange, $key);       
      }
    }

    return $queue;
  }

}
