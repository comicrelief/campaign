<?php

namespace Drupal\cr_rabbitmq\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Drupal\rabbitmq\Queue\Queue;

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
   * Execute some rabbits.
   */
  public function executeRabbit() {
    $connection = new AMQPStreamConnection(
      'localhost',
      '5672',
      'guest',
      'guest'
    );
    $channel = $connection->channel();
    $routing_key = $queue_name = 'matrix';

    $passive = FALSE;
    $durable = FALSE;
    $exclusive = FALSE;
    $auto_delete = FALSE;

    $channel->queue_declare($queue_name, $passive, $durable, $exclusive, $auto_delete);
    $message = new AMQPMessage('Hello World!');
    $channel->basic_publish($message, '', $routing_key);
    $channel->close();
    $connection->close();
  }

  public function rabbitLauncher() {
    $name = 'cr';
    $this->queueFactory = \Drupal::service('queue');
    $this->queue = $this->queueFactory->get($name);
    $this->connectionFactory = \Drupal::service('rabbitmq.connection.factory');
    $connection = $this->connectionFactory->getConnection();
    $channel = $connection->channel();
    $passive = FALSE;
    $durable = FALSE;
    $exclusive = FALSE;
    $auto_delete = FALSE;

    $channel->queue_declare($name, $passive, $durable, $exclusive, $auto_delete);
    $data = "fooo";
    $this->queue->createItem($data);

    $channel->close();
    $connection->close();
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('rabbitLauncher');
    return $events;
  }
}
