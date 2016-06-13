<?php

/**
 * @file
 * Contains RabbitMQConnection.
 */

namespace Drupal\rabbitmq;

use Drupal\Core\Site\Settings;
use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * RabbitMQ connection class.
 *
 * Related classes:
 *
 * - \Drupal\rabbitmq\Connection (this class): a factory for a
 *   \PhpAmqpLib\Connection\AMQPStreamConnection instance
 * - \PhpAmqpLib\Connection\AMQPStreamConnection: the RabbitMQ "connection",
 *   which wraps a BSD socket, handling protocol negotiation and authentication.
 *   It is not generally used as such, but as the base connection on which a
 *   "channel" is allocated. It needs a close() after use, after its channels
 *   have themselves been closed.
 * - \PhpAmqpLib\Channel\Channel: the actual communication tube, allocated on a
 *   given AMQPStreamConnection, on which "queue" insteance are declared and
 *   handled. They are created from the connection and need a close() after use.
 * - \AMQPQueue: a queue similar to the ones expected by the Drupal Queue API.
 * - \PhpAmqpLib\Message; this is the payload published over a queue by
 *   producers, and which consumers receive in their $item->data.
 *
 * This class is not tied with the Drupal Queue API.
 */
class Connection {
  const DEFAULT_SERVER_ALIAS = 'localhost';
  const DEFAULT_HOST = self::DEFAULT_SERVER_ALIAS;
  const DEFAULT_PORT = 5672;
  const DEFAULT_USER = 'guest';
  const DEFAULT_PASS = 'guest';

  /**
   * The singleton RabbitMQ connection.
   *
   * @var \PhpAmqpLib\Connection\AMQPStreamConnection
   */
  protected static $connection;

  /**
   * The settings service.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Site\Settings $settings
   *   The settings service.
   */
  public function __construct(Settings $settings) {
    // Cannot continue if the library wasn't loaded.
    assert('class_exists("\PhpAmqpLib\Connection\AMQPStreamConnection")',
      'Could not find php-amqplib. See the rabbitmq/README.md file for details.'
    );

    // @TODO investigate why is going on here
    if (empty($settings->host)) {
      $this->settings = Settings::get('rabbitmq_credentials');
    }
    else {
      $this->settings = $settings;
    }
  }

  /**
   * Get a configured connection to RabbitMQ.
   */
  public function getConnection() {
    if (!self::$connection) {
      $default_credentials = [
        'host' => static::DEFAULT_SERVER_ALIAS,
        'port' => static::DEFAULT_PORT,
        'username' => static::DEFAULT_USER,
        'password' => static::DEFAULT_PASS,
        'vhost' => '/',
      ];

      $credentials = empty($this->settings['host']) ? $default_credentials : $this->settings;
      $connection = new AMQPStreamConnection(
        $credentials['host'],
        $credentials['port'], $credentials['username'],
        $credentials['password'], $credentials['vhost']
      );

      self::$connection = $connection;
    }

    return self::$connection;
  }

}
