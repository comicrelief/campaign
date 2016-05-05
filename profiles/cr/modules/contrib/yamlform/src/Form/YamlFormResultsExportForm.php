<?php

/**
 * @file
 * Contains Drupal\yamlform\Form\YamlFormResultsExportForm.
 */

namespace Drupal\yamlform\Form;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormInterface;
use Drupal\yamlform\YamlFormSubmissionExporter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for YAML form results export form.
 */
class YamlFormResultsExportForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'yamlform_results_export';
  }

  /**
   * The YAML form submission exporter.
   *
   * @var \Drupal\yamlform\YamlFormSubmissionExporter
   */
  protected $exporter;

  /**
   * Constructs a new YamlFormResultsExportForm object.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionExporter $yamlform_submission_exporter
   *   The YAML form submission exported.
   */
  public function __construct(YamlFormSubmissionExporter $yamlform_submission_exporter) {
    $this->exporter = $yamlform_submission_exporter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('yamlform_submission.exporter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, YamlFormInterface $yamlform = NULL) {
    // Save the YAML form.
    $form_state->set('yamlform', $yamlform);

    // Set the merged default (global setting) and saved (Yaml form) values
    // into the form's state.
    $default_values = $this->config('yamlform.settings')->get('export');
    $saved_values = $yamlform->getState('export', []);
    $form_state->setValues(NestedArray::mergeDeep($default_values, $saved_values));

    // Build the form.
    $this->exporter->buildForm($form, $form_state, $yamlform);

    // Build actions.
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Download'),
      '#button_type' => 'primary',
    ];
    $form['actions']['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save settings'),
      '#submit' => ['::save'],
    ];
    $form['actions']['delete'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete settings'),
      '#attributes' => [
        'class' => ['button', 'button--danger'],
      ],
      '#access' => ($saved_values) ? TRUE : FALSE,
      '#submit' => ['::delete'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $this->exporter->getFormValues($form_state);

    // Implode exclude columns.
    $values['excluded_columns'] = implode(',', $values['excluded_columns']);

    $route_parameters = ['yamlform' => $this->getRouteMatch()->getRawParameter('yamlform')];
    $route_options = ['query' => $values];
    $form_state->setRedirect('entity.yamlform.results_export', $route_parameters, $route_options);
  }

  /**
   * Form save configuration handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function save(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    $yamlform = $form_state->get('yamlform');

    // Save the export options to the YAML form's state.
    $values = $this->exporter->getFormValues($form_state);
    $yamlform->setState('export', $values);

    drupal_set_message($this->t('The download settings have been saved.'));
  }

  /**
   * Form delete configuration handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function delete(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    $yamlform = $form_state->get('yamlform');
    $yamlform->deleteState('export');
    drupal_set_message($this->t('The download settings have been deleted.'));
  }

}
