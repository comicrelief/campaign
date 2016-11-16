<?php

/**
 * @file
 * Contains \Drupal\purge\Logger\LoggerChannelPartFactory.
 */

namespace Drupal\purge\Logger;

use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\purge\Logger\LoggerChannelPart;

/**
 * Provides a factory that creates LoggerChannelPartInterface instances.
 */
class LoggerChannelPartFactory extends ServiceProviderBase implements LoggerChannelPartFactoryInterface {

  /**
   * The single and central logger channel used by purge module(s).
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $loggerChannelPurge;

  /**
   * Construct \Drupal\purge\Logger\LoggerChannelPartFactory.
   *
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger_channel_purge
   *   The single and central logger channel used by purge module(s).
   */
  function __construct(LoggerChannelInterface $logger_channel_purge) {
    $this->loggerChannelPurge = $logger_channel_purge;
  }

  /**
   * {@inheritdoc}
   */
  public function create($id, array $grants = []) {
    return new LoggerChannelPart($this->loggerChannelPurge, $id, $grants);
  }

}
