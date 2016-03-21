<?php

/**
 * @file
 * Drupal\cr_rabbitmq\EventSubscriber\RabbitmqSubscriber.
 */

namespace Drupal\cr_rabbitmq\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Defines a queue.
 */
class RabbitmqSubscriber implements EventSubscriberInterface {

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
   * Launch a rabbit.
   */
  public function rabbitLauncher() {
    $name = 'cr';
    $this->queueFactory = \Drupal::service('queue');
    $this->queue = $this->queueFactory->get($name);
    $this->connectionFactory = \Drupal::service('rabbitmq.connection.factory');
    $connection = $this->connectionFactory->getConnection();
    $channel = $connection->channel();

    $data = 'Hello World!';
    $this->queue->createItem($data);

    $channel->close();
    $connection->close();
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('rabbitLauncher');
    return $events;
  }

}
