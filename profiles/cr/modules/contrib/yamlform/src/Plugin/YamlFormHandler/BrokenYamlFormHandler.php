<?php

namespace Drupal\yamlform\Plugin\YamlFormHandler;

use Drupal\yamlform\YamlFormHandlerBase;

/**
 * Defines a fallback plugin for missing form handler plugins.
 *
 * @YamlFormHandler(
 *   id = "broken",
 *   label = @Translation("Broken/Missing"),
 *   category = @Translation("Broken"),
 *   description = @Translation("Broken/missing form handler plugin."),
 *   cardinality = \Drupal\yamlform\YamlFormHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\yamlform\YamlFormHandlerInterface::RESULTS_IGNORED,
 * )
 */
class BrokenYamlFormHandler extends YamlFormHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function isEnabled() {
    return FALSE;
  }

}
