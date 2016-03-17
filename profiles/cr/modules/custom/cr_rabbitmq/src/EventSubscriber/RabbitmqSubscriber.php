<?php

namespace Drupal\cr_rabbitmq\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;
use Drupal\rabbitmq\Queue\Queue;
use Drupal\rabbitmq\Queue\QueueFactory;

class RabbitmqSubscriber implements EventSubscriberInterface {

  /**
   * The default queue, handled by Beanstalkd.
   *
   * @var \Drupal\beanstalkd\Queue\BeanstalkdQueue
   */
  protected $queue;
  /**
   * Server factory.
   *
   * @var \Drupal\rabbitmq\Connection
   */
  protected $connectionFactory;

  /**
   * Initialize a server and free channel.
   *
   * @return \AMQPChannel
   *   A channel to the default queue.
   */
  protected function initChannel() {
    // $this->connectionFactory = $container->get('rabbitmq.connection.factory');
    $this->connectionFactory = \Drupal::service('rabbitmq.connection.factory');
    $connection = $this->connectionFactory->getConnection();
    $channel = $connection->channel();
    $name = 'loca';
    $passive = FALSE;
    $durable = FALSE;
    $exclusive = FALSE;
    $auto_delete = FALSE;

    $channel->queue_declare($name, $passive, $durable, $exclusive, $auto_delete);

    return $channel;
  }

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
    $this->initChannel();
    $this->queue->createItem('foo');
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    //$events[KernelEvents::REQUEST][] = array('executeRabbit');
    $events[KernelEvents::REQUEST][] = array('rabbitLauncher');
    return $events;
  }
}
