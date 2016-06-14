<?php

namespace Drupal\cr_email_signup\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * A Node Publisher that publishes nodes on CRON run.
 *
 * @QueueWorker(
 *   id = "esu",
 *   title = @Translation("Email Sign Up")
 * )
 */
class EsuQueue extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * ReportWorkerBase constructor.
   *
   * @param array $configuration
   *   The configuration of the instance.
   * @param $plugin_id
   *   The plugin id.
   * @param $plugin_definition
   *   The plugin definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
  }

}
