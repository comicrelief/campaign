<?php

/**
 * @file
 * Contains \Drupal\yamlform\YamlFormSubmissionGenerate.
 */

namespace Drupal\yamlform;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Utility\Token;

/**
 * YAML form submission generation service.
 */
class YamlFormSubmissionGenerate {

  /**
   * The configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * The YAML form element manager.
   *
   * @var \Drupal\yamlform\YamlFormElementManager
   */
  protected $elementManager;
  /**
   * Constructs a YamlFormEmailBuilder object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration object factory.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   * @param \Drupal\yamlform\YamlFormElementManager $element_manager
   *   The YAML form element manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, Token $token, YamlFormElementManager $element_manager) {
    $this->configFactory = $config_factory;
    $this->token = $token;
    $this->elementManager = $element_manager;

    $this->types = Yaml::decode($this->configFactory->get('yamlform.settings')->get('test.types') ?: '');
    $this->names = Yaml::decode($this->configFactory->get('yamlform.settings')->get('test.names') ?: '');
  }

  /**
   * Generate YAML form submission data.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   The YAML form this submission will be added to.
   *
   * @return array
   *   An associative array containing YAML form submission data.
   */
  public function getData(YamlFormInterface $yamlform) {
    $inputs = $yamlform->getFlattenedInputs();

    $token_data = ['yamlform' => $yamlform];
    $data = [];
    foreach ($inputs as $key => $input) {
      if (!empty($input['#type'])) {
        $values = $this->getTestValues($yamlform, $key, $input);
        if ($values === NULL) {
          continue;
        }

        // Get random test value.
        if (is_array($values)) {
          $value = $values[array_rand($values)];
        }
        else {
          $value = $values;
        }

        // Replace tokens.
        if (is_string($value)) {
          $value = $this->token->replace($value, $token_data);
        }
        elseif (is_array($value)) {
          foreach (array_keys($value) as $key) {
            if (is_string($value[$key])) {
              $value[$key] = $this->token->replace($value[$key], $token_data);
            }
          }
        }

        // Checkboxes or TableSelect and #multiple require an array as the
        // default value.
        if ((in_array($input['#type'], ['checkboxes', 'tableselect']) || !empty($input['#multiple'])) && !is_array($value)) {
          $value = [$value];
        }

        $data[$key] = $value;
      }
    }
    return $data;
  }

  /**
   * Get test value for a YAML form element.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A YAML form.
   * @param string $name
   *   The name of the element.
   * @param array $element
   *   The FAPI element.
   *
   * @return array|int|null
   *   An array containing multiple values or a single value.
   */
  protected function getTestValues(YamlFormInterface $yamlform, $name, array $element) {
    // Get test value from the actual element.
    if (isset($element['#test'])) {
      return $element['#test'];
    }

    // Never populate hidden and value elements.
    if (in_array($element['#type'], ['hidden', 'value'])) {
      return NULL;
    }

    // Invoke YamlFormElement::test.
    if ($test = $this->elementManager->invokeMethod('getTestValue', $element, $yamlform)) {
      return $test;
    }

    // Get test values from options.
    if (isset($element['#options'])) {
      return array_keys($element['#options']);
    }

    // Get test values using #type.
    if (isset($this->types[$element['#type']])) {
      return $this->types[$element['#type']];
    }

    // Get test values using on exact name matches.
    if (isset($this->types[$name])) {
      return $this->types[$name];
    }

    // Get test values using partial name matches.
    foreach ($this->names as $key => $values) {
      if (preg_match('/(^|_)' . $key . '(_|$)/i', $name)) {
        return $values;
      }
    }

    switch ($element['#type']) {
      case 'range';
      case 'number';
        $element += ['#min' => 1, '#max' => 10];
        return rand($element['#min'], $element['#max']);
    }

    // Return default values.
    return (isset($this->names['default'])) ? $this->names['default'] : NULL;
  }

}
