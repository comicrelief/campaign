<?php

/**
 * @file
 * Contains RabbitMQ Queue.
 */

namespace Drupal\rabbitmq\Queue;

use Drupal\Core\Queue\ReliableQueueInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * RabbitMQ queue implementation.
 */
class Queue extends QueueBase implements ReliableQueueInterface {

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Array of message objects claimed from the queue.
   */
  protected $messages = array();

  /**
   * The queue name.
   *
   * @var string
   */
  protected $name;

  /**
   * Add a queue item and store it directly to the queue.
   *
   * @param mixed $data
   *   Arbitrary data to be associated with the new task in the queue.
   *
   * @return bool
   *   TRUE if the item was successfully created and was (best effort) added
   *   to the queue, otherwise FALSE. We don't guarantee the item was
   *   committed to disk etc, but as far as we know, the item is now in the
   *   queue.
   */
  public function createItem($data) {
    $logger_args = [
      'channel' => static::LOGGER_CHANNEL,
      '%queue' => $this->name,
    ];

    try {
      $channel = $this->getChannel();
      // Data must be a string.
      $item = new AMQPMessage(serialize($data), ['delivery_mode' => 2]);
      
      // Default exchange and routing keys
      $exchange = '';
      $routing_key = $this->name;

      // Fetch exchange and routing key if defined, only consider the first routing key for now
      if (isset($this->options['routing_keys'][0])) {
        list($exchange, $routing_key) = explode('.', $this->options['routing_keys'][0]);
      }

      $channel->basic_publish($item, $exchange, $routing_key);
      $this->logger->info('Item sent to queue %queue', $logger_args);
      $result = TRUE;
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to send item to queue %queue: @message', $logger_args + array('@message' => $e->getMessage()));
      $result = FALSE;
    }

    return $result;
  }

  /**
   * Retrieve the number of items in the queue.
   *
   * This is intended to provide a "best guess" count of the number of items in
   * the queue. Depending on the implementation and the setup, the accuracy of
   * the results of this function may vary.
   *
   * e.g. On a busy system with a large number of consumers and items, the
   * result might only be valid for a fraction of a second and not provide an
   * accurate representation.
   *
   * @return int
   *   An integer estimate of the number of items in the queue.
   */
  public function numberOfItems() {
    // Retrieve information about the queue without modifying it.
    $queue_options = ['passive' => TRUE];
    $jobs = array_slice($this->getQueue($this->getChannel(), $queue_options), 1, 1);
    return empty($jobs) ? 0 : $jobs[0];
  }

  /**
   * Claim an item in the queue for processing.
   *
   * @param int $lease_time
   *   How long the processing is expected to take in seconds, defaults to an
   *   hour. After this lease expires, the item will be reset and another
   *   consumer can claim the item. For idempotent tasks (which can be run
   *   multiple times without side effects), shorter lease times would result
   *   in lower latency in case a consumer fails. For tasks that should not be
   *   run more than once (non-idempotent), a larger lease time will make it
   *   more rare for a given task to run multiple times in cases of failure,
   *   at the cost of higher latency.
   *
   * @return object|false
   *   On success we return an item object. If the queue is unable to claim an
   *   item it returns false. This implies a best effort to retrieve an item
   *   and either the queue is empty or there is some other non-recoverable
   *   problem.
   */
  public function claimItem($lease_time = 3600) {
    $this->getChannel()->basic_qos(NULL, 1, NULL);
    if (!$msg = $this->getChannel()->basic_get($this->name)) {
      return FALSE;
    }

    $msg->delivery_info['channel'] = $this->getChannel();
    $this->messages[$msg->delivery_info['delivery_tag']] = $msg;

    $item = (object) [
      'id' => $msg->delivery_info['delivery_tag'],
      'data' => unserialize($msg->body),
      'expire' => time() + $lease_time,
    ];
    $this->logger->info('Item @id claimed from @queue', [
      'channel' => static::LOGGER_CHANNEL,
      '@id' => $item->id,
      '@queue' => $this->name,
    ]);

    return $item;
  }

  /**
   * Delete a finished item from the queue.
   *
   * @param object $item
   *   An item returned by DrupalQueueInterface::claimItem().
   */
  public function deleteItem($item) {
    $this->logger->info('Item @id acknowledged from @queue', [
      'channel' => static::LOGGER_CHANNEL,
      '@id' => $item->id,
      '@queue' => $this->name,
    ]);

    /* @var \PhpAmqpLib\Channel\AMQPChannel $channel */
    $channel = $this->messages[$item->id]->delivery_info['channel'];
    $channel->basic_ack($item->id);
  }

  /**
   * Release an item that the worker could not process.
   *
   * This is so another worker can come in and process it before the timeout
   * expires.
   *
   * @param object $item
   *   An item returned by DrupalQueueInterface::claimItem().
   *
   * @return bool
   *   Always pretend to succeed. Actually, the item will be released back when
   *   the connection closes, so this just eliminates that capability to send an
   *   acknowledgement to the server which would remove the item from the queue.
   */
  public function releaseItem($item) {
    unset($this->messages[$item->id]);
    return TRUE;
  }

  /**
   * Create a queue.
   *
   * Called during installation and should be used to perform any necessary
   * initialization operations. This should not be confused with the
   * constructor for these objects, which is called every time an object is
   * instantiated to operate on a queue. This operation is only needed the
   * first time a given queue is going to be initialized (for example, to make
   * a new database table or directory to hold tasks for the queue -- it
   * depends on the queue implementation if this is necessary at all).
   */
  public function createQueue() {
    return $this->getQueue($this->getChannel());
  }

  /**
   * Delete a queue and every item in the queue.
   */
  public function deleteQueue() {
    $this->getChannel()->queue_delete($this->name);
  }

}
