<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\DateList.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a 'datelist' element.
 *
 * @YamlFormElement(
 *   id = "datelist",
 *   label = @Translation("Date list")
 * )
 */
class DateList extends DateBase {

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    $element['#element_validate'][] = [get_class($this), 'validate'];
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultValue(array &$element, $default_value) {
    if (is_string($element['#default_value'])) {
      $element['#default_value'] = ($element['#default_value']) ? DrupalDateTime::createFromTimestamp(strtotime($element['#default_value'])) : NULL;
    }

  }

}
