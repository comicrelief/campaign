<?php

namespace Drupal\block_visibility_groups_admin\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 *
 */
abstract class ConditionCreatorBase extends PluginBase implements ConditionCreatorInterface {

  use StringTranslationTrait;
  /**
   * @var \Drupal\Component\Plugin\PluginManagerInterface */
  protected $pluginManager;

  /**
   * @var \Drupal\Core\Routing\CurrentRouteMatch */
  protected $route;

  /**
   * RouteConditionCreator constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   */
  public function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->route = $configuration['route'];
  }

  /**
   *
   */
  public function createConditionConfig($plugin_info) {
    $config = $plugin_info['condition_config'];
    $config['id'] = isset($config['id']) ? $config['id'] : $this->getPluginDefinition()['condition_plugin'];
    $config['negate'] = isset($config['negate']) ? $config['negate'] : 0;
    return $config;
  }

  /**
   *
   */
  public function createConditionElements() {
    $elements = [
      '#tree' => TRUE,
      '#type' => 'fieldset',
      '#title' => $this->getPluginDefinition()['label'],
      'selected' => [
        '#type' => 'checkbox',
        '#title' => $this->getNewConditionLabel(),
      ],
    ];
    return $elements;
  }

  /**
   *
   */
  public function itemSelected($condition_info) {
    return !empty($condition_info['selected']);
  }

}
