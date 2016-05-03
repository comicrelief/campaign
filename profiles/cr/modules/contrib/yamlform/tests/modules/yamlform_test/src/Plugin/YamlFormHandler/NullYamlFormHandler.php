<?php

/**
 * @file
 * Contains \Drupal\yamlform_test\Plugin\YamlFormHandler\NullYamlFormHandler.
 */

namespace Drupal\yamlform_test\Plugin\YamlFormHandler;

use Drupal\yamlform\YamlFormHandlerBase;

/**
 * YAML form submission null handler.
 *
 * @YamlFormHandler(
 *   id = "null",
 *   label = @Translation("Null"),
 *   description = @Translation("Ignores submissions. This handler allows forms with disabled results to be tested."),
 *   cardinality = \Drupal\yamlform\YamlFormHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\yamlform\YamlFormHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class NullYamlFormHandler extends YamlFormHandlerBase {}
