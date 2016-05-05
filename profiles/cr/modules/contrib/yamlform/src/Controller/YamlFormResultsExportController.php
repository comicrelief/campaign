<?php

/**
 * @file
 * Contains \Drupal\yamlform\Controller\YamlFormResultsExportController.
 */

namespace Drupal\yamlform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\yamlform\Entity\YamlFormSubmission;
use Drupal\yamlform\YamlFormInterface;
use Drupal\yamlform\YamlFormSubmissionExporter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller routines for YAML form submission export.
 */
class YamlFormResultsExportController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The YAML form submission exporter.
   *
   * @var \Drupal\yamlform\YamlFormSubmissionExporter
   */
  protected $exporter;

  /**
   * Constructs a new YamlFormResultsExportController object.
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
   * Returns YAML form submission as a CSV.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A YAML form.
   *
   * @return array|null|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
   *   A response that renders or redirects to the CVS file.
   */
  public function index(Request $request, YamlFormInterface $yamlform) {
    $query = $request->query->all();
    unset($query['destination']);
    if ($query) {

      if (!empty($query['excluded_columns']) && is_string($query['excluded_columns'])) {
        $excluded_columns = explode(',', $query['excluded_columns']);
        $query['excluded_columns'] = array_combine($excluded_columns, $excluded_columns);
      }

      $export_options = $query + $this->exporter->getDefaultExportOptions();
      if ($this->exporter->getTotal($yamlform, $export_options) >= $this->exporter->getBatchLimit()) {
        self::batchSet($yamlform, $export_options);
        return batch_process(Url::fromRoute('entity.yamlform.results_export', ['yamlform' => $yamlform->id()]));
      }
      else {
        $this->exporter->generate($yamlform, $export_options);
        return $this->downloadFile($yamlform, $export_options);
      }

    }
    else {
      $build = $this->formBuilder()->getForm('Drupal\yamlform\Form\YamlFormResultsExportForm', $yamlform);

      // Redirect to file export.
      $file_path = file_directory_temp() . '/' . $yamlform->id() . '.csv';
      if (file_exists($file_path)) {
        $file_url = Url::fromRoute('entity.yamlform.results_export_file', ['yamlform' => $yamlform->id()], ['absolute' => TRUE])->toString();
        drupal_set_message(t('Export creation complete. Your download should begin now. If it does not start, <a href=":href">download the file here</a>. This file may only be downloaded once.', [':href' => $file_url]));
        $build['#attached']['html_head'][] = [
          [
            '#tag' => 'meta',
            '#attributes' => [
              'http-equiv' => 'refresh',
              'content' => '0; url=' . $file_url,
            ],
          ],
        ];
      }

      return $build;
    }
  }

  /**
   * Returns YAML form submission results as CSV file.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A YAML form.
   *
   * @return array|\Symfony\Component\HttpFoundation\Response
   *   A response that renders or redirects to the CVS file.
   */
  public function file(Request $request, YamlFormInterface $yamlform) {
    $file_path = $this->exporter->getFilePath($yamlform);

    if (!file_exists($file_path)) {
      $t_args = [
        ':href' => Url::fromRoute('entity.yamlform.results_export', ['yamlform' => $yamlform->id()])->toString(),
      ];
      $build = [
        '#markup' => $this->t('No export file ready for download. The file may have already been downloaded by your browser. Visit the <a href=":href">download export form</a> to create a new export.', $t_args),
      ];
      return $build;
    }
    else {
      return $this->downloadFile($yamlform);
    }
  }


  /**
   * Download generated CSV file.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A YAML form.
   * @param array $export_options
   *   An associative array of export options generated via the
   *   Drupal\yamlform\Form\YamlFormResultsExportForm.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   A response object containing the CSV file.
   */
  public function downloadFile(YamlFormInterface $yamlform, array $export_options = []) {
    $file_path = $this->exporter->getFilePath($yamlform);

    // Return the CSV files.
    $csv = file_get_contents($file_path);
    unlink($file_path);

    if (!empty($export_options['download'])) {
      $headers = [
        'Content-Length' => strlen($csv),
        'Content-Type' => 'application/force-download',
        'Content-Disposition' => 'attachment; filename="' . $yamlform->id() . '.csv"',
      ];
    }
    else {
      $headers = [
        'Content-Length' => strlen($csv),
        'Content-Type' => 'text/plain; charset=utf-8',
      ];
    }
    return new Response($csv, 200, $headers);
  }

  /****************************************************************************/
  // Batch functions.
  // Using static method to prevent the service container from being serialized.
  // "Prevents exception 'AssertionError' with message 'The container was serialized.'."
  /****************************************************************************/

  /**
   * Batch API; Initialize batch operations.
   *
   * @param \Drupal\yamlform\YamlFormInterface|NULL $yamlform
   *   A YAML form.
   * @param array $export_options
   *   An array of export options.
   *
   * @see http://www.jeffgeerling.com/blogs/jeff-geerling/using-batch-api-build-huge-csv
   */
  static public function batchSet(YamlFormInterface $yamlform, array $export_options) {
    if (!empty($export_options['excluded_columns']) && is_string($export_options['excluded_columns'])) {
      $excluded_columns = explode(',', $export_options['excluded_columns']);
      $export_options['excluded_columns'] = array_combine($excluded_columns, $excluded_columns);
    }

    /** @var \Drupal\yamlform\YamlFormSubmissionExporter $exporter */
    $exporter = \Drupal::service('yamlform_submission.exporter');

    $parameters = [
      $yamlform,
      $exporter->getFieldDefinitions($export_options),
      $exporter->getElements($yamlform, $export_options),
      $export_options,
    ];
    $batch = [
      'title' => t('Exporting submissions'),
      'init_message' => t('Creating export file'),
      'error_message' => t('The export file could not be created because an error occurred.'),
      'operations' => [
        [['\Drupal\yamlform\Controller\YamlFormResultsExportController', 'batchProcess'], $parameters],
      ],
      'finished' => ['\Drupal\yamlform\Controller\YamlFormResultsExportController', 'batchFinish'],
    ];

    batch_set($batch);
  }

  /**
   * Batch API callback; Write the header and rows of the export to the export file.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   The YAML form.
   * @param array $field_definitions
   *   YAML form submission field definitions.
   * @param array $input_columns
   *   YAML form inputs as columns.
   * @param array $export_options
   *   An associative array of export options.
   * @param mixed|array $context
   *   The batch current context.
   */
  static public function batchProcess(YamlFormInterface $yamlform, array $field_definitions, array $input_columns, array $export_options, &$context) {
    /** @var \Drupal\yamlform\YamlFormSubmissionExporter $exporter */
    $exporter = \Drupal::service('yamlform_submission.exporter');
    if (empty($context['sandbox'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_sid'] = 0;
      $context['sandbox']['max'] = $exporter->getQuery($yamlform, $export_options)->count()->execute();
      $context['results']['yamlform'] = $yamlform;
      $context['results']['export_options'] = $export_options;
      $exporter->writeHeader($yamlform, $field_definitions, $input_columns, $export_options);
    }

    // Write CSV records.
    $query = $exporter->getQuery($yamlform, $export_options);
    $query->condition('sid', $context['sandbox']['current_sid'], '>');
    $query->range(0, $exporter->getBatchLimit());
    $entity_ids = $query->execute();
    $yamlform_submissions = YamlFormSubmission::loadMultiple($entity_ids);
    $exporter->writeRecords($yamlform, $yamlform_submissions, $field_definitions, $input_columns, $export_options);

    // Track progress.
    $context['sandbox']['progress'] += count($yamlform_submissions);
    $context['sandbox']['current_sid'] = ($yamlform_submissions) ? end($yamlform_submissions)->id() : 0;

    $context['message'] = t('Exported @count of @total submissions...', ['@count' => $context['sandbox']['progress'], '@total' => $context['sandbox']['max']]);

    // Track finished.
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  /**
   * Batch API callback; Completed export.
   *
   * @param bool $success
   *   TRUE if batch successfully completed.
   * @param array $results
   *   Batch results.
   * @param array $operations
   *   An array of function calls (not used in this function).
   */
  static public function batchFinish($success, array $results, array $operations) {
    if (!$success) {
      /** @var \Drupal\yamlform\YamlFormSubmissionExporter $exporter */
      $exporter = \Drupal::service('yamlform_submission.exporter');
      $file_path = $exporter->getFilePath($results['yamlform']);
      @unlink($file_path);
      drupal_set_message(t('Finished with an error.'));
    }
  }

}
