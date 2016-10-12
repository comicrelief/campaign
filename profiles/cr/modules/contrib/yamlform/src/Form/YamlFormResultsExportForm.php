<?php

namespace Drupal\yamlform\Form;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormSubmissionExporterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for form results export form.
 */
class YamlFormResultsExportForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'yamlform_results_export';
  }

  /**
   * The form submission exporter.
   *
   * @var \Drupal\yamlform\YamlFormSubmissionExporterInterface
   */
  protected $exporter;

  /**
   * Constructs a new YamlFormResultsExportForm object.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionExporterInterface $yamlform_submission_exporter
   *   The form submission exported.
   */
  public function __construct(YamlFormSubmissionExporterInterface $yamlform_submission_exporter) {
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
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Set the merged default (global setting) and saved values into
    // the form's state.
    $default_values = $this->config('yamlform.settings')->get('export');
    $saved_values = $this->exporter->getYamlFormOptions();
    $form_state->setValues(NestedArray::mergeDeep($default_values, $saved_values));

    // Build the form.
    $this->exporter->buildForm($form, $form_state);

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
      '#value' => $this->t('Reset settings'),
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

    if ($source_entity = $this->exporter->getSourceEntity()) {
      $entity_type = $source_entity->getEntityTypeId();
      $entity_id = $source_entity->id();
      $route_parameters = [$entity_type => $entity_id];
      $route_options = ['query' => $values];
      $form_state->setRedirect('entity.' . $entity_type . '.yamlform.results_export', $route_parameters, $route_options);
    }
    elseif ($yamlform = $this->exporter->getYamlForm()) {
      $route_parameters = ['yamlform' => $yamlform->id()];
      $route_options = ['query' => $values];
      $form_state->setRedirect('entity.yamlform.results_export', $route_parameters, $route_options);
    }
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
    // Save the export options to the form's state.
    $values = $this->exporter->getFormValues($form_state);
    $this->exporter->setYamlFormOptions($values);
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
    $this->exporter->deleteYamlFormOptions();
    drupal_set_message($this->t('The download settings have been reset.'));
  }

}
