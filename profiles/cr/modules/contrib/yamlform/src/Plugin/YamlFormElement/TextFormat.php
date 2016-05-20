<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\TextFormat.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\filter\Entity\FilterFormat;
use Drupal\yamlform\YamlFormElementBase;
use Drupal\Core\Mail\MailFormatHelper;

/**
 * Provides a 'text_format' element.
 *
 * @YamlFormElement(
 *   id = "text_format",
 *   label = @Translation("Text format"),
 *   multiline = TRUE,
 * )
 */
class TextFormat extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function setDefaultValue(array &$element, $default_value) {
    if (is_array($element['#default_value'])) {
      $element['#format'] = $element['#default_value']['format'];
      $element['#default_value'] = $element['#default_value']['value'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function formatHtml(array &$element, $value, array $options = []) {
    if (isset($value['value']) && isset($value['format'])) {
      return check_markup($value['value'], $value['format']);
    }
    else {
      return $value;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function formatText(array &$element, $value, array $options = []) {
    if (isset($value['value']) && isset($value['format'])) {
      $html = check_markup($value['value'], $value['format']);
      // Convert any HTML to plain-text.
      $html = MailFormatHelper::htmlToText($html);
      // Wrap the mail body for sending.
      $html = MailFormatHelper::wrapMail($html);
      return $html;
    }
    else {
      return $value;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultFormat() {
    return filter_default_format();
  }

  /**
   * {@inheritdoc}
   */
  public function getFormats() {
    $filters = FilterFormat::loadMultiple();
    $formats = parent::getFormats();
    foreach ($filters as $filter) {
      $formats[$filter->id()] = $filter->label();
    }
    return $formats;
  }

}
