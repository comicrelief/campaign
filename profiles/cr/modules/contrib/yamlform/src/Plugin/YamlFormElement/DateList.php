<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a 'datelist' element.
 *
 * @YamlFormElement(
 *   id = "datelist",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Datetime!Element!Datelist.php/class/Datelist",
 *   label = @Translation("Date list"),
 *   category = @Translation("Date/time elements"),
 * )
 */
class DateList extends DateBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return parent::getDefaultProperties() + [
      'date_part_order' => [
        'year',
        'month',
        'day',
        'hour',
        'minute',
      ],
      'date_text_parts' => [
        'year',
      ],
      'date_year_range' => '1900:2050',
      'date_increment' => 1,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    parent::prepare($element, $yamlform_submission);
    $element['#element_validate'][] = [get_class($this), 'validate'];
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultValue(array &$element) {
    if (!empty($element['#default_value']) && is_string($element['#default_value'])) {
      $element['#default_value'] = ($element['#default_value']) ? DrupalDateTime::createFromTimestamp(strtotime($element['#default_value'])) : NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getElementSelectorInputsOptions(array $element) {
    $date_parts = (isset($element['#date_part_order'])) ? $element['#date_part_order'] : ['year', 'month', 'day', 'hour', 'minute'];

    $t_args = ['@title' => $this->getAdminLabel($element)];
    $selectors = [
      'day' => $this->t('@title days', $t_args),
      'month' => $this->t('@title months', $t_args),
      'year' => $this->t('@title years', $t_args),
      'hour' => $this->t('@title hours', $t_args),
      'minute' => $this->t('@title minutes', $t_args),
      'second' => $this->t('@title seconds', $t_args),
      'ampm' => $this->t('@title am/pm', $t_args),
    ];

    $selectors = array_intersect_key($selectors, array_combine($date_parts, $date_parts));
    foreach ($selectors as &$selector) {
      $selector .= ' [' . $this->t('Select') . ']';
    }
    return $selectors;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['date'] = [
      '#type' => 'details',
      '#title' => $this->t('Date list settings'),
      '#open' => FALSE,
    ];
    $form['date']['date_part_order_label'] = [
      '#type' => 'item',
      '#title' => $this->t('Date part and order'),
      '#description' => $this->t("Select the date parts and order that should be used in the element."),
      '#access' => TRUE,
    ];
    $form['date']['date_part_order'] = [
      '#type' => 'yamlform_tableselect_sort',
      '#header' => ['part' => 'Date part'],
      '#options' => [
        'day' => ['part' => $this->t('Days')],
        'month' => ['part' => $this->t('Months')],
        'year' => ['part' => $this->t('Years')],
        'hour' => ['part' => $this->t('Hours')],
        'minute' => ['part' => $this->t('Minutes')],
        'second' => ['part' => $this->t('Seconds')],
        'ampm' => ['part' => $this->t('AM/PM')],
      ],
    ];
    $form['date']['date_text_parts'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Date text parts'),
      '#description' => $this->t("Select date parts that should be presented as text fields instead of drop-down selectors."),
      '#options' => [
        'day' => $this->t('Days'),
        'month' => $this->t('Months'),
        'year' => $this->t('Years'),
        'hour' => $this->t('Hours'),
        'minute' => $this->t('Minutes'),
        'second' => $this->t('Seconds'),
      ],
    ];
    $form['date']['date_year_range'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date year range'),
      '#description' => $this->t("A description of the range of years to allow, like '1900:2050', '-3:+3' or '2000:+3', where the first value describes the earliest year and the second the latest year in the range."),
    ];
    $form['date']['date_increment'] = [
      '#type' => 'number',
      '#title' => $this->t('Date increment'),
      '#description' => $this->t('The increment to use for minutes and seconds'),
      '#min' => 1,
      '#size' => 4,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::validateConfigurationForm($form, $form_state);
    $values = $form_state->getValues();
    $values['date_part_order'] = array_values($values['date_part_order']);
    $values['date_text_parts'] = array_values(array_filter($values['date_text_parts']));
    $form_state->setValues($values);
  }

  /**
   * {@inheritdoc}
   */
  protected function setConfigurationFormDefaultValue(array &$form, array &$element, array &$property_element, $property_name) {
    if (in_array($property_name, ['date_text_parts', 'date_part_order'])) {
      $element[$property_name] = array_combine($element[$property_name], $element[$property_name]);
    }
    parent::setConfigurationFormDefaultValue($form, $element, $property_element, $property_name);
  }

}
