<?php

/**
 * @file
 * Contains \Drupal\yamlform\Controller\YamlFormPluginHandlerController.
 */

namespace Drupal\yamlform\Controller;

/**
 * Controller for all YAML form handlers.
 */
class YamlFormPluginHandlerController extends YamlFormPluginBaseController {

  /**
   * {@inheritdoc}
   */
  public static $pluginName = 'handler';

  /**
   * {@inheritdoc}
   */
  public function index() {
    $plugin_definitions = $this->pluginManager->getDefinitions();

    $rows = [];
    foreach ($plugin_definitions as $plugin_id => $plugin_definition) {
      $rows[$plugin_id] = [
        $plugin_id,
        $plugin_definition['label'],
        $plugin_definition['description'],
        ($plugin_definition['cardinality'] == -1) ? $this->t('Unlimited') : $plugin_definition['cardinality'],
        $plugin_definition['results'] ? $this->t('Processed') : $this->t('Ignored'),
        $plugin_definition['provider'],
      ];
    }

    ksort($rows);
    return [
      '#type' => 'table',
      '#header' => [
        $this->t('ID'),
        $this->t('Label'),
        $this->t('Description'),
        $this->t('Cardinality'),
        $this->t('Results'),
        $this->t('Provided by'),
      ],
      '#rows' => $rows,
      '#empty' => $this->t('There are no enabled %name plugins.', ['%name' => static::$pluginName]),
    ];
  }

}
