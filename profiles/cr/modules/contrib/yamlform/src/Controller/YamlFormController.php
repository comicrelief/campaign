<?php

namespace Drupal\yamlform\Controller;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\yamlform\YamlFormInterface;
use Drupal\yamlform\YamlFormMessageManagerInterface;
use Drupal\yamlform\YamlFormRequestInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides route responses for form.
 */
class YamlFormController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Form request handler.
   *
   * @var \Drupal\yamlform\YamlFormRequestInterface
   */
  protected $requestHandler;

  /**
   * Form message manager.
   *
   * @var \Drupal\yamlform\YamlFormMessageManagerInterface
   */
  protected $messageManager;

  /**
   * Constructs a new YamlFormSubmissionController object.
   *
   * @param \Drupal\yamlform\YamlFormRequestInterface $request_handler
   *   The form request handler.
   */
  public function __construct(YamlFormRequestInterface $request_handler, YamlFormMessageManagerInterface $message_manager) {
    $this->requestHandler = $request_handler;
    $this->messageManager = $message_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('yamlform.request'),
      $container->get('yamlform.message_manager')
    );
  }

  /**
   * Returns a form to add a new submission to a form.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   The form this submission will be added to.
   *
   * @return array
   *   The form submission form.
   */
  public function addForm(Request $request, YamlFormInterface $yamlform) {
    return $yamlform->getSubmissionForm();
  }

  /**
   * Returns a form confirmation page.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param \Drupal\yamlform\YamlFormInterface|null $yamlform
   *   A form.
   *
   * @return array
   *   A render array representing a form confirmation page
   */
  public function confirmation(Request $request, YamlFormInterface $yamlform = NULL) {
    /** @var \Drupal\Core\Entity\EntityInterface $source_entity */
    if (!$yamlform) {
      list($yamlform, $source_entity) = $this->requestHandler->getYamlFormEntities();
    }
    else {
      $source_entity = $this->requestHandler->getCurrentSourceEntity('yamlform');
    }

    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
    $yamlform_submission = NULL;

    if ($token = $request->get('token')) {
      /** @var \Drupal\yamlform\YamlFormSubmissionStorageInterface $yamlform_submission_storage */
      $yamlform_submission_storage = $this->entityTypeManager()->getStorage('yamlform_submission');
      if ($entities = $yamlform_submission_storage->loadByProperties(['token' => $token])) {
        $yamlform_submission = reset($entities);
      }
    }

    $settings = $yamlform->getSettings();

    $build = [];

    $build['#yamlform'] = $yamlform;
    $build['#yamlform_submission'] = $yamlform_submission;

    $build['#title'] = $yamlform->label();

    $this->messageManager->setYamlForm($yamlform);
    $this->messageManager->setYamlFormSubmission($yamlform_submission);

    // Add wizard progress tracker to the form.
    if ($settings['wizard_complete'] && ($settings['wizard_progress_bar'] || $settings['wizard_progress_pages'] || $settings['wizard_progress_percentage'])) {
      $build['progress'] = [
        '#theme' => 'yamlform_progress',
        '#yamlform' => $yamlform,
        '#current_page' => 'complete',
      ];
    }

    $build['confirmation'] = $this->messageManager->build(YamlFormMessageManagerInterface::SUBMISSION_CONFIRMATION);

    // Apply all passed query string parameters to the 'Back to form' link.
    $query = $request->query->all();
    unset($query['yamlform_id']);
    $options = ($query) ? ['query' => $query] : [];

    // Link back to the source URL or the main form.
    if ($source_entity) {
      $url = $source_entity->toUrl('canonical', $options);
    }
    elseif ($yamlform_submission) {
      $url = $yamlform_submission->getSourceUrl();
    }
    else {
      $url = $yamlform->toUrl('canonical', $options);
    }

    $build['back_to'] = [
      '#prefix' => '<p>',
      '#suffix' => '</p>',
      '#type' => 'link',
      '#title' => $this->t('Back to form'),
      '#url' => $url,
    ];

    return $build;
  }

  /**
   * Returns a form filter form autocomplete matches.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param bool $templates
   *   If TRUE, limit autocomplete matches to form templates.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public function autocomplete(Request $request, $templates = FALSE) {
    $q = $request->query->get('q');

    $yamlform_storage = $this->entityTypeManager()->getStorage('yamlform');

    $query = $yamlform_storage->getQuery()
      ->condition('title', $q, 'CONTAINS')
      ->sort('title');

    // Limit query to templates.
    if ($templates) {
      $query->condition('template', TRUE);
    }

    $entity_ids = $query->execute();

    if (empty($entity_ids)) {
      return new JsonResponse([]);
    }
    $yamlforms = $yamlform_storage->loadMultiple($entity_ids);

    $matches = [];
    foreach ($yamlforms as $yamlform) {
      if ($yamlform->access('view')) {
        $value = new FormattableMarkup('@label (@id)', ['@label' => $yamlform->label(), '@id' => $yamlform->id()]);
        $matches[] = ['value' => $value, 'label' => $value];
      }
    }

    return new JsonResponse($matches);
  }

  /**
   * Route title callback.
   *
   * @param \Drupal\yamlform\YamlFormInterface|null $yamlform
   *   A form.
   *
   * @return string
   *   The form label as a render array.
   */
  public function title(YamlFormInterface $yamlform = NULL) {
    /** @var \Drupal\Core\Entity\EntityInterface $source_entity */
    if (!$yamlform) {
      list($yamlform, $source_entity) = $this->requestHandler->getYamlFormEntities();
    }
    else {
      $source_entity = $this->requestHandler->getCurrentSourceEntity('yamlform');
    }
    return ($source_entity) ? $source_entity->label() : $yamlform->label();
  }

}
