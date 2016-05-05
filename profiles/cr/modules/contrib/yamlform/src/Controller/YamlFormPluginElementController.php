<?php

/**
 * @file
 * Contains \Drupal\yamlform\Controller\YamlFormPluginElementController.
 */

namespace Drupal\yamlform\Controller;

/**
 * Controller for all YAML form elements.
 */
class YamlFormPluginElementController extends YamlFormPluginBaseController {

  /**
   * {@inheritdoc}
   */
  protected static $pluginName = 'element';

  /**
   * {@inheritdoc}
   */
  public function index() {
    $plugin_definitions = $this->pluginManager->getDefinitions();

    $rows = [];
    foreach ($plugin_definitions as $plugin_id => $plugin_definition) {
      /** @var \Drupal\yamlform\YamlFormElementInterface $element */
      $element = $this->pluginManager->createInstance($plugin_id);
      $default_format = $element->getDefaultFormat();
      $format_names = array_keys($element->getFormats());
      $formats = array_combine($format_names, $format_names);
      if (isset($formats[$default_format])) {
        $formats[$default_format] = '<b>' . $formats[$default_format] . '</b>';
      }
      $rows[$plugin_id] = [
        $plugin_definition['label'],
        $plugin_id,
        $plugin_definition['multiline'] ? t('Yes') : t('No'),
        ['data' => ['#markup' => implode('; ', $formats)]],
        $plugin_definition['provider'],
      ];
    }

    ksort($rows);
    return [
      '#type' => 'table',
      '#header' => [
        $this->t('Label'),
        $this->t('Name'),
        $this->t('Multiline'),
        $this->t('Formats'),
        $this->t('Provided by'),
      ],
      '#rows' => $rows,
      '#empty' => $this->t('There are no enabled %name plugins.', ['%name' => static::$pluginName]),
    ];
  }

}
