<?php

namespace Drupal\yamlform\Controller;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\yamlform\YamlFormSubmissionInterface;
use Drupal\yamlform\YamlFormRequestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides route responses for form submissions.
 */
class YamlFormSubmissionController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Form request handler.
   *
   * @var \Drupal\yamlform\YamlFormRequestInterface
   */
  protected $requestHandler;

  /**
   * Constructs a new YamlFormSubmissionController object.
   *
   * @param \Drupal\yamlform\YamlFormRequestInterface $request_handler
   *   The form request handler.
   */
  public function __construct(YamlFormRequestInterface $request_handler) {
    $this->requestHandler = $request_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('yamlform.request')
    );
  }

  /**
   * Returns a form submission in a specified format type.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A form submission.
   * @param string $type
   *   The format type.
   *
   * @return array
   *   A render array representing a form submission in a specified format
   *   type.
   */
  public function index(YamlFormSubmissionInterface $yamlform_submission, $type) {
    if ($type == 'default') {
      $type = 'html';
    }

    $build = [];
    $source_entity = $this->requestHandler->getCurrentSourceEntity('yamlform_submission');
    // Navigation.
    $build['navigation'] = [
      '#theme' => 'yamlform_submission_navigation',
      '#yamlform_submission' => $yamlform_submission,
    ];

    // Information.
    $build['information'] = [
      '#theme' => 'yamlform_submission_information',
      '#yamlform_submission' => $yamlform_submission,
      '#source_entity' => $source_entity,
      '#open' => FALSE,
    ];

    // Submission.
    $build['submission'] = [
      '#theme' => 'yamlform_submission_' . $type,
      '#yamlform_submission' => $yamlform_submission,
      '#source_entity' => $source_entity,
    ];

    // Wrap plain text and YAML in CodeMirror view widget.
    if (in_array($type, ['text', 'yaml'])) {
      $build['submission'] = [
        '#theme' => 'yamlform_codemirror',
        '#code' => $build['submission'],
        '#type' => $type,
      ];
    }

    $build['#attached']['library'][] = 'yamlform/yamlform.admin';

    return $build;
  }

  /**
   * Toggle form submission sticky.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A form submission.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An AJAX response that toggle the sticky icon.
   */
  public function sticky(YamlFormSubmissionInterface $yamlform_submission) {
    // Toggle sticky.
    $yamlform_submission->setSticky(!$yamlform_submission->isSticky())->save();

    // Get state.
    $state = $yamlform_submission->isSticky() ? 'on' : 'off';

    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand(
      '#yamlform-submission-' . $yamlform_submission->id() . '-sticky',
      new FormattableMarkup('<span class="yamlform-icon yamlform-icon-sticky yamlform-icon-sticky--@state"></span>', ['@state' => $state])
    ));
    return $response;

  }

  /**
   * Route title callback.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   The form submission.
   *
   * @return array
   *   The form submission as a render array.
   */
  public function title(YamlFormSubmissionInterface $yamlform_submission) {
    $source_entity = $this->requestHandler->getCurrentSourceEntity('yamlform_submission');
    $t_args = [
      '@form' => ($source_entity) ? $source_entity->label() : $yamlform_submission->getYamlForm()->label(),
      '@id' => $yamlform_submission->serial(),
    ];
    return $this->t('@form: Submission #@id', $t_args);
  }

}
