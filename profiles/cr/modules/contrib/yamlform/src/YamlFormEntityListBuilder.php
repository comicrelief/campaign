<?php
/**
 * @file
 * Contains Drupal\yamlform\YamlFormEntityListBuilder.
 */

namespace Drupal\yamlform;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of YAML form entities.
 *
 * @see \Drupal\yamlform\Entity\YamlForm
 */
class YamlFormEntityListBuilder extends ConfigEntityListBuilder {

  /**
   * Search keys.
   *
   * @var string
   */
  protected $keys;

  /**
   * YAML form submission storage.
   *
   * @var \Drupal\yamlform\YamlFormSubmissionStorageInterface
   */
  protected $submissionStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage) {
    parent::__construct($entity_type, $storage);
    $this->keys = \Drupal::request()->query->get('search');
    $this->submissionStorage = \Drupal::entityManager()->getStorage('yamlform_submission');
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    // Add the filter.
    $build['filter_form'] = \Drupal::formBuilder()->getForm('\Drupal\yamlform\Form\YamlFormFilterForm', $this->t('forms'), $this->t('Filter by title, description, or inputs'), $this->keys);

    // Display info.
    if ($total = $this->getTotal()) {
      $t_args = [
        '@total' => $total,
        '@results' => $this->formatPlural($total, $this->t('form'), $this->t('forms')),
      ];
      $build['info'] = [
        '#markup' => $this->t('@total @results', $t_args),
        '#prefix' => '<div>',
        '#suffix' => '</div>',
      ];
    }

    $build += parent::render();
    return $build;
  }
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['title'] = [
      'data' => $this->t('Title'),
    ];
    $header['description'] = [
      'data' => $this->t('Description'),
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];
    $header['status'] = [
      'data' => $this->t('Status'),
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];
    $header['results_total'] = [
      'data' => $this->t('Total Results'),
      'class' => [RESPONSIVE_PRIORITY_MEDIUM],
    ];
    $header['results_operations'] = [
      'data' => $this->t('Operations'),
      'class' => [RESPONSIVE_PRIORITY_MEDIUM],
    ];
    $header['operations'] = [
      'data' => '',
    ];
    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\yamlform\YamlFormInterface */
    $settings = $entity->getSettings();

    // ISSUE: YAML forms that the current user can't access are not being hidden via the EntityQuery.
    // WORK-AROUND: Don't link the YAML for.
    // See: Access control is not applied to config entity queries
    // https://www.drupal.org/node/2636066
    $row['title'] = ($entity->access('view')) ? $entity->toLink() : $entity->label();
    $row['description']['data']['description']['#markup'] = $entity->get('description');
    $row['status'] = $entity->isOpen() ? $this->t('Open') : $this->t('Closed');
    $row['results_total'] = $this->submissionStorage->getTotal($entity) . (!empty($settings['results_disabled']) ? ' ' . $this->t('(Disabled)') : '');
    $row['results_operations']['data'] = [
      '#type' => 'operations',
      '#links' => $this->getDefaultOperations($entity, 'results'),
    ];
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity, $type = 'edit') {
    $route_parameters = ['yamlform' => $entity->id()];
    if ($type == 'results') {
      $operations = [];
      if ($entity->access('submission_view_any')) {
        $operations['submissions'] = [
          'title' => $this->t('Submissions'),
          'url' => Url::fromRoute('entity.yamlform.results_submissions', $route_parameters),
        ];
        $operations['table'] = [
          'title' => $this->t('Table'),
          'url' => Url::fromRoute('entity.yamlform.results_table', $route_parameters),
        ];
        $operations['export'] = [
          'title' => $this->t('Export'),
          'url' => Url::fromRoute('entity.yamlform.results_export', $route_parameters),
        ];
      }
      if ($entity->access('submission_delete_any')) {
        $operations['clear'] = [
          'title' => $this->t('Clear'),
          'url' => Url::fromRoute('entity.yamlform.results_clear', $route_parameters),
        ];
      }
    }
    else {
      $operations = parent::getDefaultOperations($entity);
      if ($entity->access('view')) {
        $operations['view'] = [
          'title' => $this->t('View'),
          'weight' => 20,
          'url' => Url::fromRoute('entity.yamlform.canonical', $route_parameters),
        ];
      }
      if ($entity->access('submission_update_any')) {
        $operations['test'] = [
          'title' => $this->t('Test'),
          'weight' => 21,
          'url' => Url::fromRoute('entity.yamlform.test', $route_parameters),
        ];
      }
      if ($entity->access('export') && $entity->hasLinkTemplate('export-form')) {
        $operations['export'] = [
          'title' => $this->t('Export'),
          'weight' => 22,
          'url' => Url::fromRoute('entity.yamlform.export_form', $route_parameters),
        ];
      }
      if ($entity->access('duplicate') && $entity->hasLinkTemplate('duplicate-form')) {
        $operations['duplicate'] = [
          'title' => $this->t('Duplicate'),
          'weight' => 23,
          'url' => Url::fromRoute('entity.yamlform.duplicate_form', $route_parameters),
        ];
      }
    }
    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    return $this->getQuery()
      ->sort('title')
      ->pager($this->limit)
      ->execute();
  }

  /**
   * Get the total number of submissions.
   *
   * @return int
   *   The total number of submissions.
   */
  protected function getTotal() {
    return $this->getQuery()
      ->count()
      ->execute();
  }

  /**
   * Get the base entity query filtered by YAML form and search.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   An entity query.
   */
  protected function getQuery() {
    $query = $this->getStorage()->getQuery();

    // Filter forms.
    if ($this->keys) {
      $or = $query->orConditionGroup()
        ->condition('title', $this->keys, 'CONTAINS')
        ->condition('description', $this->keys, 'CONTAINS')
        ->condition('inputs', $this->keys, 'CONTAINS');
      $query->condition($or);
    }

    return $query;
  }

}
