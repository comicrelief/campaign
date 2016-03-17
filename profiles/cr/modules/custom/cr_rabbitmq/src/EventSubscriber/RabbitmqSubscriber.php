<?php

namespace Drupal\cr_rabbitmq\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitmqSubscriber implements EventSubscriberInterface {

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

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('executeRabbit');
    return $events;
  }
}