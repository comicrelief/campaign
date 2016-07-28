<?php

/**
 * @file
 * Contains \Drupal\yamlform\Controller\YamlFormPluginBaseController.
 */

namespace Drupal\yamlform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Controller for all YAML form handlers.
 */
abstract class YamlFormPluginBaseController extends ControllerBase {

  /**
   * The name of the YAML form plugin.
   *
   * @var string
   */
  protected static $pluginName;

  /**
   * A YAML form plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $pluginManager;

  /**
   * Constructs a YamlFormPluginBaseController object.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager
   *   A YAML form plugin manager.
   */
  public function __construct(PluginManagerInterface $plugin_manager) {
    $this->pluginManager = $plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.yamlform.' . static::$pluginName));
  }

  /**
   * Displays a page with an overview of all available YAML form handler plugins.
   */
  abstract public function index();

}
