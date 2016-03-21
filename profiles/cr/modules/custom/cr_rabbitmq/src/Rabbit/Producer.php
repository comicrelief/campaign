<?php
/**
 * @file
 * Contains \Drupal\cr_rabbitmq\Rabbit\Producer.
 */

namespace Drupal\cr_rabbitmq\Rabbit;

/**
 * Producer class.
 */
class Producer {
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
   * Server factory.
   *
   * @var \Drupal\rabbitmq\Connection
   */
  protected $connectionFactory;

  /**
   * Send data to the rabbitmq.
   */
  public function sendMq($data) {
    $name = 'cr';
    $this->queueFactory = \Drupal::service('queue');
    $this->queue = $this->queueFactory->get($name);
    $this->connectionFactory = \Drupal::service('rabbitmq.connection.factory');
    $connection = $this->connectionFactory->getConnection();
    $channel = $connection->channel();
    $this->queue->createItem($data);
    $channel->close();
    $connection->close();
  }

}
