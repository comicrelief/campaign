<?php

/**
 * @file
 * Contains \Drupal\Core\Logger\MonologLoggerChannelFactory.
 */

namespace Drupal\monolog\Logger;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Defines a factory for logging channels.
 */
class MonologLoggerChannelFactory implements LoggerChannelFactoryInterface, ContainerAwareInterface {
  use ContainerAwareTrait;

  /**
   * Array of all instantiated logger channels keyed by channel name.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface[]
   */
  protected $channels = array();

  /**
   * Array of enabled processors.
   *
   * @var array
   */
  protected $enabledProcessors;

  /**
   * {@inheritdoc}
   */
  public function get($channel) {
    if (!isset($this->channels[$channel])) {
      try {
        $this->channels[$channel] = $this->getChannelInstance($channel);
      }
      catch (\InvalidArgumentException $e) {
        $this->channels[$channel] = new NullLogger();
        if ($this->container->get('current_user')->hasPermission('administer site configuration')) {
          drupal_set_message($e->getMessage(), 'error');
        }
      }
    }

    return $this->channels[$channel];
  }

  /**
   * {@inheritdoc}
   */
  public function addLogger(LoggerInterface $logger, $priority = 0) {
    // No-op, we have handlers which are services and configured in the services.yml file.
  }

  /**
   * Factory function for Monolog loggers.
   *
   * @param string $channel_name
   *   The name the logging channel.
   *
   * @return \Psr\Log\LoggerInterface
   *
   * @throws \RuntimeException
   * @throws \InvalidArgumentException
   */
  protected function getChannelInstance($channel_name) {
    if (!class_exists('Monolog\Logger')) {
      throw new \RuntimeException('The Monolog\Logger class was not found. Make sure the Monolog package is installed via Composer.');
    }

    if (!$this->container) {
      // We need the container to read parameters etc.
      return new NullLogger();
    }

    $logger = new Logger($channel_name);
    $parameters = $this->container->getParameter('monolog.channel_handlers');
    $handlers = array_key_exists($channel_name, $parameters) ? $parameters[$channel_name] : $parameters['default'];

    foreach ($handlers as $handler) {
      $logger->pushHandler($this->container->get('monolog.handler.' . $handler));
    }

    foreach ($this->container->getParameter('monolog.processors') as $processor) {
      $logger->pushProcessor($this->container->get('monolog.processor.' . $processor));
    }

    return $logger;
  }

}
