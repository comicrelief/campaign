<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\yamlform\YamlFormElementBase;
use Drupal\yamlform\YamlFormInterface;
use Drupal\Component\Utility\Unicode;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a 'table' element.
 *
 * @YamlFormElement(
 *   id = "table",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Table.php/class/Table",
 *   label = @Translation("Table"),
 *   category = @Translation("Table"),
 * )
 */
class Table extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return [
      'header' => [],
      'empty' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isInput(array $element) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isContainer(array $element) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    parent::prepare($element, $yamlform_submission);

    // Add .js-form.wrapper to fix #states handling.
    $element['#attributes']['class'][] = 'js-form-wrapper';

    // Disable #tree for table element. Forms do not support the #tree
    // property.
    $element['#tree'] = FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function build($format, array &$element, $value, array $options = []) {
    return parent::build($format, $element, $value, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultFormat() {
    return 'table';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormats() {
    return ['table'];
  }

  /**
   * {@inheritdoc}
   */
  public function getTestValue(array $element, YamlFormInterface $yamlform) {
    // Containers should never have values and therefore should never have
    // a test value.
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function formatHtml(array &$element, $value, array $options = []) {
    // Undo form submission elements and convert rows back into a simple
    // render array.
    $rows = [];
    foreach ($value as $row_key => $row_element) {
      $element[$row_key] = [];
      foreach ($row_element['#value'] as $column_key => $column_element) {
        if (is_string($column_element['#value']) || $column_element['#value'] instanceof TranslatableMarkup) {
          $value = ['#markup' => $column_element['#value']];
        }
        else {
          $value = $column_element['#value'];
        }
        $rows[$row_key][$column_key] = ['data' => $value];
      }
    }
    return $rows + $element;
  }

  /**
   * {@inheritdoc}
   */
  public function formatText(array &$element, $value, array $options = []) {
    // Render the HTML table.
    $build = $this->formatHtml($element, $value, $options);
    $html = \Drupal::service('renderer')->renderPlain($build);

    // Convert table in pipe delimited plain text.
    $html = preg_replace('#\s*</td>\s*<td[^>]*>\s*#', ' | ', $html);
    $html = preg_replace('#\s*</th>\s*<th[^>]*>\s*#', ' | ', $html);
    $html = preg_replace('#^\s+#m', '', $html);
    $html = preg_replace('#\s+$#m', '', $html);
    $html = preg_replace('#\n+#s', "\n", $html);
    $html = strip_tags($html);

    // Remove blank links from text.
    // From: http://stackoverflow.com/questions/709669/how-do-i-remove-blank-lines-from-text-in-php
    $html = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $html);

    // Add divider between (optional) header.
    if (!empty($element['#header'])) {
      $lines = explode("\n", trim($html));
      $lines[0] .= "\n" . str_repeat('-', Unicode::strlen($lines[0]));
      $html = implode("\n", $lines);
    }

    return $html;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['table'] = [
      '#type' => 'details',
      '#title' => $this->t('Table settings'),
      '#open' => TRUE,
    ];
    $form['table']['header'] = [
      '#title' => $this->t('Header (YAML)'),
      '#type' => 'yamlform_codemirror',
      '#mode' => 'yaml',
    ];
    $form['table']['empty'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Empty text'),
      '#description' => $this->t('Text to display when no rows are present.'),
    ];
    return $form;
  }

}
