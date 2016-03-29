<?php

/**
 * @file
 * Contains the RabbitMQ QueueFactory.
 *
 * @author: Frédéric G. MARAND <fgm@osinet.fr>
 *
 * @copyright (c) 2015 Ouest Systèmes Informatiques (OSInet).
 *
 * @license General Public License version 2 or later
 */

namespace Drupal\rabbitmq\Queue;

use Doctrine\Common\Util\Debug;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\rabbitmq\Connection;
use Psr\Log\LoggerInterface;

/**
 * Class RabbitMQ QueueFactory.
 *
 * @package Drupal\rabbitmq\Queue
 */
class QueueFactory {
  const SERVICE_NAME = 'queue.rabbitmq';
  const DEFAULT_QUEUE_NAME = 'default';

  /**
   * The server factory service.
   *
   * @var \Drupal\rabbitmq\Connection
   */
  protected $connectionFactory;

  /**
   * The logger service for the RabbitMQ channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The module_handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $modules;

  /**
   * Constructor.
   *
   * @param \Drupal\rabbitmq\Connection $connection_factory
   *   The connection factory service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $modules
   *   The module handler service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger service for the RabbitMQ channel.
   */
  public function __construct(Connection $connection_factory,
    ModuleHandlerInterface $modules, LoggerInterface $logger) {
    $this->connectionFactory = $connection_factory;
    $this->logger = $logger;
    $this->modules = $modules;
  }

  /**
   * Constructs a new queue object for a given name.
   *
   * @param string $name
   *   The name of the Queue holding key and value pairs.
   *
   * @return Queue
   *   The Queue object
   */
  public function get($name) {
    $queue = new Queue($name, $this->connectionFactory, $this->modules, $this->logger);
    return $queue;
  }

}
