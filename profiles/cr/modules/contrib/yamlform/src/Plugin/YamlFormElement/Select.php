<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\Select.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a 'select' element.
 *
 * @YamlFormElement(
 *   id = "select",
 *   label = @Translation("Select")
 * )
 */
class Select extends OptionsBase {

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    if (empty($element['#multiple'])) {
      if (!isset($element['#empty_option'])) {
        $element['#empty_option'] = empty($element['#required']) ? $this->t('- Select -') : $this->t('- None -');
      }
    }
    else {
      if (!isset($element['#empty_option'])) {
        $element['#empty_option'] = empty($element['#required']) ? $this->t('- None -') : NULL;
      }
      parent::prepare($element, $yamlform_submission);
    }
  }

}
