<?php

/**
 * @file
 * Contains \Drupal\monolog\MonologServiceProvider.
 */

namespace Drupal\monolog;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Overrides the logger.factory service with the monolog factory.
 */
class MonologServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('logger.factory');
    $definition->setClass('Drupal\monolog\Logger\MonologLoggerChannelFactory')
      ->clearTags();
  }

}
