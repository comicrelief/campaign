<?php

namespace Drupal\yamlform\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\yamlform\YamlFormRequestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a confirmation form for deleting a form submission.
 */
class YamlFormSubmissionDeleteForm extends ContentEntityDeleteForm {

  /**
   * The form entity.
   *
   * @var \Drupal\yamlform\YamlFormInterface
   */
  protected $yamlform;


  /**
   * The form submission entity.
   *
   * @var \Drupal\yamlform\YamlFormSubmissionInterface
   */
  protected $yamlformSubmission;

  /**
   * The form source entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $sourceEntity;

  /**
   * Form request handler.
   *
   * @var \Drupal\yamlform\YamlFormRequestInterface
   */
  protected $requestHandler;

  /**
   * Constructs a new YamlFormSubmissionDeleteForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\yamlform\YamlFormRequestInterface $request_handler
   *   The form request handler.
   */
  public function __construct(EntityManagerInterface $entity_manager, YamlFormRequestInterface $request_handler) {
    parent::__construct($entity_manager);
    $this->requestHandler = $request_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('yamlform.request')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    list($this->yamlformSubmission, $this->sourceEntity) = $this->requestHandler->getYamlFormSubmissionEntities();
    $this->yamlform = $this->yamlformSubmission->getYamlForm();
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete @title?', ['@title' => $this->yamlformSubmission->label()]);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDeletionMessage() {
    return $this->t('@title has been deleted.', ['@title' => $this->yamlformSubmission->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    $route_name = $this->requestHandler->getRouteName($this->yamlform, $this->sourceEntity, 'yamlform.results_submissions');
    $route_parameters = $this->requestHandler->getRouteParameters($this->yamlform, $this->sourceEntity);
    return new Url($route_name, $route_parameters);
  }

  /**
   * {@inheritdoc}
   */
  protected function getRedirectUrl() {
    return $this->getCancelUrl();
  }

  /**
   * {@inheritdoc}
   */
  protected function logDeletionMessage() {
    // Deletion logging is handled via YamlFormSubmissionStorage.
    // @see \Drupal\yamlform\YamlFormSubmissionStorage::delete
  }

}
