<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormElementBase;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a base 'date' class.
 */
abstract class DateBase extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    // Don't used 'datetime_wrapper', instead use 'form_element' wrapper.
    // @see \Drupal\Core\Datetime\Element\Datelist
    // @see \Drupal\yamlform\Plugin\YamlFormElement\DateTime
    $element['#theme_wrappers'] = ['form_element'];

    // Must manually process #states.
    // @see drupal_process_states().
    if (isset($element['#states'])) {
      $element['#attached']['library'][] = 'core/drupal.states';
      $element['#wrapper_attributes']['data-drupal-states'] = Json::encode($element['#states']);
    }
    parent::prepare($element, $yamlform_submission);

    // Parse #default_value date input format.
    $this->parseInputFormat($element, '#default_value');
  }

  /**
   * {@inheritdoc}
   */
  public function formatText(array &$element, $value, array $options = []) {
    $timestamp = strtotime($value);
    if (empty($timestamp)) {
      return $value;
    }

    $format = $this->getFormat($element) ?: $this->getHtmlDateFormat($element);
    if (DateFormat::load($format)) {
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

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    // Append supported date input format to #default_value description.
    $form['general']['default_value']['#description'] .= '<br />' . $this->t('Accepts any date in any <a href=":href">GNU Date Input Format</a>. Strings such as today, +2 months, and Dec 9 2004 are all valid.', [':href' => 'http://www.gnu.org/software/tar/manual/html_chapter/Date-input-formats.html']);

    // Allow custom date formats to be entered.
    $form['display']['format']['#type'] = 'yamlform_select_other';
    $form['display']['format']['#other__option_label'] = $this->t('Custom date format...');
    $form['display']['format']['#other__description'] = $this->t('A user-defined date format. See the <a href="http://php.net/manual/function.date.php">PHP manual</a> for available options.');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $properties = $this->getConfigurationFormProperties($form, $form_state);
    if ($properties['#default_value'] && strtotime($properties['#default_value']) === FALSE) {
      $this->setInputFormatError($form['properties']['general']['default_value'], $form_state);
    }
    parent::validateConfigurationForm($form, $form_state);
  }

  /**
   * Get an HTML date/time format for an element.
   *
   * @param array $element
   *   An element.
   *
   * @return string
   *   An HTML date/time format string.
   */
  protected function getHtmlDateFormat(array $element) {
    if ($element['#type'] == 'datelist') {
      return (isset($element['#date_part_order']) && !in_array('hour', $element['#date_part_order'])) ? 'html_date' : 'html_datetime';
    }
    else {
      return 'html_' . $element['#type'];
    }
  }

  /**
   * Parse GNU Date Input Format.
   *
   * @param array $element
   *   An element.
   * @param string $property
   *   The element's date property.
   */
  protected function parseInputFormat(array &$element, $property) {
    if (!isset($element[$property])) {
      return;
    }

    $timestamp = strtotime($element[$property]);
    if ($timestamp === FALSE) {
      $element[$property] = NULL;
    }
    else {
      $element[$property] = \Drupal::service('date.formatter')->format($timestamp, $this->getHtmlDateFormat($element));
    }
  }

  /**
   * Set GNU input format error.
   *
   * @param array $element
   *   The property element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  protected function setInputFormatError(array $element, FormStateInterface $form_state) {
    $t_args = [
      '@title' => $element['#title'] ?: $element['#key'],
      ':href' => 'http://www.gnu.org/software/tar/manual/html_chapter/Date-input-formats.html',
    ];
    $form_state->setError($element, $this->t('The @title could not be interpreted in <a href=":href">GNU Date Input Format</a>.', $t_args));
  }

}
