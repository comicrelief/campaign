<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Component\Render\HtmlEscapedText;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'textarea' element.
 *
 * @YamlFormElement(
 *   id = "textarea",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Textarea.php/class/Textarea",
 *   label = @Translation("Textarea"),
 *   category = @Translation("Basic elements"),
 *   multiline = TRUE,
 * )
 */
class Textarea extends TextBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return [
      'title' => '',
      'description' => '',

      'required' => FALSE,
      'required_error' => '',
      'default_value' => '',

      'title_display' => '',
      'description_display' => '',
      'field_prefix' => '',
      'field_suffix' => '',
      'placeholder' => '',

      'unique' => FALSE,

      'admin_title' => '',
      'private' => FALSE,

      'format' => $this->getDefaultFormat(),

      'counter_type' => '',
      'counter_maximum' => '',
      'counter_message' => '',
      'rows' => '',

      'wrapper_attributes__class' => '',
      'wrapper_attributes__style' => '',
      'attributes__class' => '',
      'attributes__style' => '',

      'flex' => 1,
      'states' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function formatHtml(array &$element, $value, array $options = []) {
    $build = [
      '#markup' => nl2br(new HtmlEscapedText($value)),
    ];
    return \Drupal::service('renderer')->renderPlain($build);
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['general']['default_value']['#type'] = 'textarea';
    return $form;
  }

}
