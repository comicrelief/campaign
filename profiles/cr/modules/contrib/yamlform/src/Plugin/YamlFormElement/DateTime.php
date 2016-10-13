<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a 'datetime' element.
 *
 * @YamlFormElement(
 *   id = "datetime",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Datetime!Element!Datetime.php/class/Datetime",
 *   label = @Translation("Date/time"),
 *   category = @Translation("Date/time elements"),
 * )
 */
class DateTime extends DateBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    $date_format = '';
    $time_format = '';
    // Date formats cannot be loaded during install or update.
    if (!defined('MAINTENANCE_MODE')) {
      /** @var $date_format_entity \Drupal\Core\Datetime\DateFormatInterface */
      if ($date_format_entity = DateFormat::load('html_date')) {
        $date_format = $date_format_entity->getPattern();
      }
      /** @var $time_format_entity \Drupal\Core\Datetime\DateFormatInterface */
      if ($time_format_entity = DateFormat::load('html_time')) {
        $time_format = $time_format_entity->getPattern();
      }
    }

    return parent::getDefaultProperties() + [
      'date_date_format' => $date_format,
      'date_date_element' => 'date',
      'date_date_callbacks' => [],
      'date_time_format' => $time_format,
      'date_time_element' => 'time',
      'date_time_callbacks' => [],
      'date_year_range' => '1900:2050',
      'date_increment' => 1,
      'date_timezone' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    parent::prepare($element, $yamlform_submission);
    // Must define a '#default_value' for Datetime element to prevent the
    // below error.
    // Notice: Undefined index: #default_value in Drupal\Core\Datetime\Element\Datetime::valueCallback().
    if (!isset($element['#default_value'])) {
      $element['#default_value'] = NULL;
    }
    $element['#element_validate'][] = [get_class($this), 'validate'];
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultValue(array &$element) {
    if (is_string($element['#default_value']) && !empty($element['#default_value'])) {
      $element['#default_value'] = ($element['#default_value']) ? DrupalDateTime::createFromTimestamp(strtotime($element['#default_value'])) : NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getElementSelectorInputsOptions(array $element) {
    $t_args = ['@title' => $this->getAdminLabel($element)];
    return [
      'date' => $this->t('@title [Date]', $t_args),
      'time' => $this->t('@title [Time]', $t_args),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    // Date.
    $form['date'] = [
      '#type' => 'details',
      '#title' => $this->t('Date/time settings'),
      '#description' => $this->t('Datetime element is designed to have sane defaults so any or all can be omitted.') . ' ' .
      $this->t('Both the date and time components are configurable so they can be output as HTML5 datetime elements or not, as desired.'),
      '#open' => FALSE,
    ];
    $form['date']['date_date_element'] = [
      '#type' => 'select',
      '#title' => t('Date element'),
      '#options' => [
        'datetime' => $this->t('HTML datetime - Use the HTML5 datetime element type.'),
        'datetime-local' => $this->t('HTML datetime input (localized) - Use the HTML5 datetime-local element type.'),
        'date' => $this->t('HTML date input - Use the HTML5 date element type.'),
        'text' => $this->t('Text input - No HTML5 element, use a normal text field.'),
        'none' => $this->t('None - Do not display a date element'),
      ],
    ];
    $form['date']['date_date_format'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date format'),
      '#description' => $this->t('A date format string that describes the format that should be displayed to the end user for the date.') . ' ' .
      $this->t('When using HTML5 elements the format MUST use the appropriate HTML5 format for that element, no other format will work.') . ' ' .
      $this->t('See the format_date() function for a list of the possible formats and HTML5 standards for the HTML5 requirements.') . ' ' .
      $this->t('Defaults to the right HTML5 format for the chosen element if a HTML5 element is used, otherwise defaults to HTML Date (Y-m-d).'),
      '#states' => [
        'invisible' => [
          ':input[name="properties[date_date_element]"]' => ['value' => 'none'],
        ],
      ],
    ];
    $form['date']['date_year_range'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date year range'),
      '#description' => $this->t("A description of the range of years to allow, like '1900:2050', '-3:+3' or '2000:+3', where the first value describes the earliest year and the second the latest year in the range.") . ' ' .
      $this->t('A year in either position means that specific year.') . ' ' .
      $this->t('A +/- value describes a dynamic value that is that many years earlier or later than the current year at the time the form is displayed.') . ' ' .
      $this->t("Used in jQueryUI (fallback) datepicker year range and HTML5 min/max date settings. Defaults to '1900:2050'."),
      '#states' => [
        'invisible' => [
          ':input[name="properties[date_date_element]"]' => ['value' => 'none'],
        ],
      ],
    ];

    // Time.
    $form['date']['date_time_element'] = [
      '#type' => 'select',
      '#title' => t('Time element'),
      '#options' => [
        'time' => $this->t('HTML time input - Use a HTML5 time element type.'),
        'text' => $this->t('Text input - No HTML5 element, use a normal text field.'),
        'none' => $this->t('None - Do not display a time element.'),
      ],
      '#states' => [
        'invisible' => [
          [':input[name="properties[date_date_element]"]' => ['value' => 'datetime']],
          'xor',
          [':input[name="properties[date_date_element]"]' => ['value' => 'datetime-local']],
        ],
      ],
    ];
    $form['date']['date_time_format'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Time format'),
      '#description' => $this->t('A date format string that describes the format that should be displayed to the end user for the time.') . ' ' .
      $this->t('When using HTML5 elements the format MUST use the appropriate HTML5 format for that element, no other format will work.') . ' ' .
      $this->t('See the format_date() function for a list of the possible formats and HTML5 standards for the HTML5 requirements.') . ' ' .
      $this->t('Defaults to the right HTML5 format for the chosen element if a HTML5 element is used, otherwise defaults to HTML Time (H:i:s).'),
      '#states' => [
        'invisible' => [
          [':input[name="properties[date_date_element]"]' => ['value' => 'datetime']],
          'xor',
          [':input[name="properties[date_date_element]"]' => ['value' => 'datetime-local']],
          'xor',
          [':input[name="properties[date_time_element]"]' => ['value' => 'none']],
        ],
      ],
    ];
    $form['date']['date_increment'] = [
      '#type' => 'number',
      '#title' => $this->t('Date increment'),
      '#description' => $this->t("The increment to use for minutes and seconds, i.e. '15' would show only :00, :15, :30 and :45. Used for HTML5 step values and jQueryUI (fallback) datepicker settings. Defaults to 1 to show every minute."),
      '#min' => 1,
      '#states' => [
        'invisible' => [
          [':input[name="properties[date_date_element]"]' => ['value' => 'datetime']],
          'xor',
          [':input[name="properties[date_date_element]"]' => ['value' => 'datetime-local']],
          'xor',
          [':input[name="properties[date_time_element]"]' => ['value' => 'none']],
        ],
      ],
    ];
    $form['date']['date_timezone'] = [
      '#type' => 'select',
      '#title' => $this->t('Date timezone override'),
      '#options' => system_time_zones(TRUE),
      '#description' => $this->t('Generally this should be left empty and it will be set correctly for the user using the form.') . ' ' .
      $this->t('Useful if the default value is empty to designate a desired timezone for dates created in form processing.') . ' ' .
      $this->t('If a default date is provided, this value will be ignored, the timezone in the default date takes precedence.') . ' ' .
      $this->t('Defaults to the value returned by drupal_get_user_timezone().'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurationFormProperties(array &$form, FormStateInterface $form_state) {
    $properties = parent::getConfigurationFormProperties($form, $form_state);
    // Remove hidden date properties.
    if (isset($properties['#date_date_element'])) {
      switch ($properties['#date_date_element']) {
        case 'datetime':
        case 'datetime-local':
          unset(
            $properties['#date_time_element'],
            $properties['#date_time_format'],
            $properties['#date_increment']
          );
          break;

        case 'none':
          unset(
            $properties['#date_date_format'],
            $properties['#date_year_range']
          );
          break;
      }
    }

    // Remove hidden date properties.
    if (isset($properties['#date_time_element']) && $properties['#date_time_element'] == 'none') {
      unset(
        $properties['#date_time_format'],
        $properties['date_increment']
      );
    }

    return $properties;
  }

}
