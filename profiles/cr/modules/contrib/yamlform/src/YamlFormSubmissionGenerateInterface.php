<?php

namespace Drupal\yamlform;

/**
 * Defines an interface for form submission generation.
 *
 * @see \Drupal\yamlform\YamlFormSubmissionGenerate
 * @see \Drupal\yamlform\Plugin\DevelGenerate\YamlFormSubmissionDevelGenerate
 */
interface YamlFormSubmissionGenerateInterface {

  /**
   * Generate form submission data.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   The form this submission will be added to.
   *
   * @return array
   *   An associative array containing form submission data.
   */
  public function getData(YamlFormInterface $yamlform);

  /**
   * Get test value for a form element.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A form.
   * @param string $name
   *   The name of the element.
   * @param array $element
   *   The FAPI element.
   *
   * @return array|int|null
   *   An array containing multiple values or a single value.
   */
  public function getTestValue(YamlFormInterface $yamlform, $name, array $element);

}
