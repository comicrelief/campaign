<?php

/**
 * @file
 * Drush plugin for RabbitMQ.
 */

use Drupal\Core\Queue\QueueWorkerInterface;
use Drupal\rabbitmq\Connection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Yaml\Yaml;

/**
 * Implements hook_drush_command().
 */
function rabbitmq_drush_command() {
  $file = preg_replace('/(inc|php)$/', 'yml', __FILE__);
  $config = Yaml::parse(file_get_contents($file));
  $items = $config['commands'];
  return $items;
}

/**
 * Command callback for rabbitmq-test-producer.
 */
function drush_rabbitmq_test_producer() {
  $connection = new AMQPStreamConnection(
    Connection::DEFAULT_HOST,
    Connection::DEFAULT_PORT,
    Connection::DEFAULT_USER,
    Connection::DEFAULT_PASS
  );
  $channel = $connection->channel();
  $routing_key = $queue_name = 'hello';
  $channel->queue_declare($queue_name, FALSE, FALSE, FALSE, FALSE);
  $message = new AMQPMessage('Hello World!');
  $channel->basic_publish($message, '', $routing_key);
  echo " [x] Sent 'Hello World!'\n";
  $channel->close();
  $connection->close();
}

/**
 * Command callback for rabbitmq-test-consumer.
 */
function drush_rabbitmq_test_consumer() {
  $connection = new AMQPStreamConnection(
    Connection::DEFAULT_HOST,
    Connection::DEFAULT_PORT,
    Connection::DEFAULT_USER,
    Connection::DEFAULT_PASS
  );
  $channel = $connection->channel();
  $queue_name = 'hello';
  $channel->queue_declare($queue_name, FALSE, FALSE, FALSE, FALSE);
  echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

  $callback = function($msg) {
    echo " [x] Received ", $msg->body, "\n";
  };

  $channel->basic_consume($queue_name, '', FALSE, TRUE, FALSE, FALSE, $callback);

  while (count($channel->callbacks)) {
    $channel->wait();
  }
  $channel->close();
  $connection->close();
}

/**
 * Command callback for rabbitmq-queue-info.
 */
function drush_rabbitmq_queue_info($queue_name = NULL) {
  if (empty($queue_name)) {
    return;
  }

  /* @var \Drupal\Core\Queue\QueueFactory $queue_factory */
  $queue_factory = \Drupal::service('queue');

  $queue = $queue_factory->get($queue_name);
  $count = $queue->numberOfItems();
  echo Yaml::dump([$queue_name => $count]);
}

/**
 * Command callback: Create a queue worker.
 */
function drush_rabbitmq_worker($queue_name) {
  /* @var \Drupal\Core\Queue\QueueWorkerManager $worker_manager */
  $worker_manager = \Drupal::service('plugin.manager.queue_worker');

  $workers = $worker_manager->getDefinitions();

  if (!isset($workers[$queue_name])) {
    return drush_set_error('rabbitmq', t('No known worker for queue @queue', [
      '@queue' => $queue_name,
    ]));
  }
  $max_iterations = intval(drush_get_option('max_iterations', 0));
  $memory_limit = intval(drush_get_option('memory_limit', -1));

  // Before we start listening for messages, make sure the callback
  // worker is callable.
  $worker = $worker_manager->createInstance($queue_name);
  if (!($worker instanceof QueueWorkerInterface)) {
    return drush_set_error("Worker for queue does not implement the worker interface.");
  }

  /* @var \Drupal\Core\Queue\QueueFactory $queue_factory */
  $queue_factory = \Drupal::service('queue');
  /* @var \Drupal\rabbitmq\Queue\queue $queue */
  $queue = $queue_factory->get($queue_name);
  assert('$queue instanceof \Drupal\rabbitmq\Queue\Queue');

  /** @var \Psr\Log\LoggerInterface $logger */
  $logger = \Drupal::service('logger.channel.rabbitmq-drush');

  $callback = function (AMQPMessage $msg) use ($worker, $queue_name, $logger) {
    $logger->info('(Drush) Received queued message: @id', [
      '@id' => $msg->delivery_info['delivery_tag'],
    ]);

    try {
      // Build the item to pass to the queue worker.
      $item = (object) [
        'id' => $msg->delivery_info['delivery_tag'],
        'data' => unserialize($msg->body),
      ];

      // Call the queue worker.
      $worker->processItem($item->data);

      // Remove the item from the queue.
      $msg->delivery_info['channel']->basic_ack($item->id);
      $logger->info('(Drush) Item @id acknowledged from @queue', [
        '@id' => $item->id,
        '@queue' => $queue_name,
      ]);
    }
    catch (\Exception $e) {
      watchdog_exception('rabbitmq', $e);
      $msg->delivery_info['channel']->basic_reject($msg->delivery_info['delivery_tag'], TRUE);
    }
  };

  $queue->getChannel()->basic_qos(NULL, 1, NULL);
  $queue->getChannel()->basic_consume($queue_name, '', FALSE, FALSE, FALSE, FALSE, $callback);

  if ($max_iterations > 0) {
    $ready_message = "RabbitMQ worker ready to receive up to @count messages.";
    $ready_args = ['@count' => $max_iterations];
  }
  else {
    $ready_message = "RabbitMQ worker ready to receive an unlimited number of messages.";
    $ready_args = [];
  }
  $logger->info($ready_message, $ready_args, WATCHDOG_INFO);

  // Begin listening for messages to process.
  $iteration = 0;
  while (count($queue->getChannel()->callbacks)) {
    $queue->getChannel()->wait();

    // Evaluate whether worker should exit.
    // If the --memory_limit option is set, check the memory usage and exit if
    // the limit has been exceeded or met.
    if ($memory_limit > 0) {
      $memory_usage = memory_get_peak_usage() / 1024 / 1024;
      if ($memory_usage >= $memory_limit) {
        $logger->notice('RabbitMQ worker has reached or exceeded set memory limit of @limitMB and will now exit.', [
          '@limit' => $memory_limit,
        ]);
        break;
      }
    }

    // If max_iterations is set, don't allow loop to continue longer than
    // max_iterations.
    $iteration++;
    if ($max_iterations > 0) {
      if ($max_iterations <= $iteration) {
        $logger->notice('Rabbit worker has reached max number of iterations: @count. Exiting.', $ready_args);
        break;
      }
    }
  }
}
