<?php

/**
 * @file
 * API for rabbitmq.
 */

/**
 * Provides options for RabbitMQ queues.
 *
 * @return array<string,array<string,boolean|null>>
 *   A hash of queue options, indexed by queue name.
 */
function hook_rabbitmq_queue_info() {
  $queue_name = 'q_foo';

  $queue_options = [
    'passive' => FALSE,
    // Whether the queue is persistent or not. A durable queue is slower but
    // can survive if RabbitMQ fails.
    'durable' => TRUE,
    'exclusive' => FALSE,
    'auto_delete' => FALSE,
    'nowait' => FALSE,
    'arguments' => NULL,
    'ticket' => NULL,
  ];

  return [
    $queue_name => $queue_options,
  ];
}
