<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\DateBase.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormElementBase;

/**
 * Provides a base 'date' class.
 */
abstract class DateBase extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function formatText(array &$element, $value, array $options = []) {
    $timestamp = strtotime($value);
    if (empty($timestamp)) {
      return $value;
    }

    $format = $this->getFormat($element);
    if (empty($format)) {
      switch ($element['#type']) {
        case 'datelist':
          $format = (isset($element['#date_part_order']) && !in_array($element['#date_part_order'], 'hour')) ? 'html_date' : 'html_datetime';
          break;

        default:
          $format = 'html_' . $element['#type'];
          break;
      }
      return \Drupal::service('date.formatter')->format($timestamp, $format);
    }
    elseif (DateFormat::load($format)) {
      return \Drupal::service('date.formatter')->format($timestamp, $format);
    }
    else {
      return date($format, $timestamp);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFormat(array $element) {
    if (isset($element['#format'])) {
      return $element['#format'];
    }
    elseif (isset($element['#date_format'])) {
      return $element['#date_format'];
    }
    else {
      return parent::getFormat($element);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultFormat() {
    return 'fallback';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormats() {
    $formats = parent::getFormats();
    $date_formats = DateFormat::loadMultiple();
    foreach ($date_formats as $date_format) {
      $formats[$date_format->id()] = $date_format->label();
    }
    return $formats;
  }

  /**
   * Form API callback. Convert DrupalDateTime array and object to ISO datetime.
   */
  public static function validate(array &$element, FormStateInterface $form_state) {
    $name = $element['#name'];
    $value = $form_state->getValue($name);
    /** @var \Drupal\Core\Datetime\DrupalDateTime $datetime */
    if ($datetime = $value['object']) {
      $form_state->setValue($name, $datetime->format('c') ?: '');
    }
    else {
      $form_state->setValue($name, '');
    }
  }

}
