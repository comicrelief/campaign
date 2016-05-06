<?php

/**
 * @file
 * Contains \Drupal\yamlform\Controller\YamlFormController.
 */

namespace Drupal\yamlform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides route responses for YAML form submissions.
 */
class YamlFormSubmissionController extends ControllerBase {

  /**
   * Returns a YAML form submission in a specified format type.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   * @param string $type
   *   The format type.
   *
   * @return array
   *   A render array representing a YAML form submission in a specified format
   *   type.
   */
  public function index(YamlFormSubmissionInterface $yamlform_submission, $type) {
    $build = [];

    // Navigation.
    $build['navigation'] = [
      '#theme' => 'yamlform_submission_navigation',
      '#yamlform_submission' => $yamlform_submission,
      '#rel' => $type,
    ];

    // Information.
    $build['information'] = [
      '#theme' => 'yamlform_submission_information',
      '#yamlform_submission' => $yamlform_submission,
      '#open' => FALSE,
    ];

    // Submission.
    $build['submission'] = [
      '#theme' => 'yamlform_codemirror',
      '#code' => [
        '#theme' => 'yamlform_submission_' . $type,
        '#yamlform_submission' => $yamlform_submission,
      ],
      '#type' => $type,
    ];

    // Libraries.
    $build['#attached']['library'][] = 'yamlform/codemirror.' . $type;

    return $build;
  }

  /**
   * Route title callback.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   The YAML form submission.
   *
   * @return array
   *   The YAML form submission as a render array.
   */
  public function title(YamlFormSubmissionInterface $yamlform_submission) {
    return $this->t(
      '@title: Submission #@id', ['@title' => $yamlform_submission->getYamlForm()->label(), '@id' => $yamlform_submission->id()]
    );
  }

}
