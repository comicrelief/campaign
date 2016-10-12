<?php

namespace Drupal\yamlform;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Url;
use Drupal\yamlform\Utility\YamlFormDialogHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Defines a class to build a listing of form entities.
 *
 * @see \Drupal\yamlform\Entity\YamlForm
 */
class YamlFormEntityListBuilder extends ConfigEntityListBuilder {

  /**
   * Form state open.
   */
  const STATE_OPEN = 'open';

  /**
   * Form state closed.
   */
  const STATE_CLOSED = 'closed';

  /**
   * Search keys.
   *
   * @var string
   */
  protected $keys;

  /**
   * Search state.
   *
   * @var string
   */
  protected $state;

  /**
   * Form submission storage.
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
    $this->state = \Drupal::request()->query->get('state');
    $this->submissionStorage = \Drupal::entityTypeManager()->getStorage('yamlform_submission');
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    // Handler autocomplete redirect.
    if ($this->keys && preg_match('#\(([^)]+)\)$#', $this->keys, $match)) {
      if ($yamlform = $this->getStorage()->load($match[1])) {
        return new RedirectResponse($yamlform->toUrl()->setAbsolute(TRUE)->toString());
      }
    }

    $build = [];

    // Must manually add local actions to the form because we can't alter local
    // actions and add the needed dialog attributes.
    // @see https://www.drupal.org/node/2585169
    if ($this->moduleHandler()->moduleExists('yamlform_ui')) {
      $add_form_attributes = YamlFormDialogHelper::getModalDialogAttributes(400, ['button', 'button-action', 'button--primary', 'button--small']);
    }
    else {
      $add_form_attributes = ['class' => ['button', 'button-action', 'button--primary', 'button--small']];
    }

    if (\Drupal::currentUser()->hasPermission('create yamlform')) {
      $build['local_actions'] = [
        'add_form' => [
          '#type' => 'link',
          '#title' => $this->t('Add form'),
          '#url' => new Url('entity.yamlform.add_form'),
          '#attributes' => $add_form_attributes,
        ],
      ];
    }

    // Add the filter by key(word) and/or state.
    $state_options = [
      '' => $this->t('All [@total]', ['@total' => $this->getTotal(NULL, NULL)]),
      YamlFormEntityListBuilder::STATE_OPEN => $this->t('Open [@total]', ['@total' => $this->getTotal(NULL, self::STATE_OPEN)]),
      YamlFormEntityListBuilder::STATE_CLOSED => $this->t('Closed [@total]', ['@total' => $this->getTotal(NULL, self::STATE_CLOSED)]),
    ];
    $build['filter_form'] = \Drupal::formBuilder()->getForm('\Drupal\yamlform\Form\YamlFormEntityFilterForm', $this->keys, $this->state, $state_options);

    // Display info.
    if ($this->isAdmin()) {
      if ($total = $this->getTotal($this->keys, $this->state)) {
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
    }
    $build += parent::render();
    $build['#attached']['library'][] = 'yamlform/yamlform.admin';
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
    $header['author'] = [
      'data' => $this->t('Author'),
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

    // ISSUE: Forms that the current user can't access are not being hidden via the EntityQuery.
    // WORK-AROUND: Don't link to the form.
    // See: Access control is not applied to config entity queries
    // https://www.drupal.org/node/2636066
    $row['title']['data']['title'] = ['#markup' => ($entity->access('view')) ? $entity->toLink()->toString() : $entity->label()];
    if ($entity->isTemplate()) {
      $row['title']['data']['template'] = ['#markup' => ' <b>(' . $this->t('Template') . ')</b>'];
    }
    $row['description']['data']['description']['#markup'] = $entity->get('description');
    $row['status'] = $entity->isOpen() ? $this->t('Open') : $this->t('Closed');
    $row['owner'] = ($owner = $entity->getOwner()) ? $owner->toLink() : '';
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
    /* @var $entity \Drupal\yamlform\YamlFormInterface */
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
          'title' => $this->t('Download'),
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
      if ($entity->access('duplicate')) {
        $operations['duplicate'] = [
          'title' => $this->t('Duplicate'),
          'weight' => 23,
          'url' => Url::fromRoute('entity.yamlform.duplicate_form', $route_parameters),
          'attributes' => YamlFormDialogHelper::getModalDialogAttributes(400),
        ];
      }
    }
    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    return $this->getQuery($this->keys, $this->state)
      ->sort('title')
      ->pager($this->getLimit())
      ->execute();
  }

  /**
   * Get the total number of submissions.
   *
   * @param string $keys
   *   (optional) Search key.
   * @param string $state
   *   (optional) Form state. Can be 'open' or 'closed'.
   *
   * @return int
   *   The total number of submissions.
   */
  protected function getTotal($keys = '', $state = '') {
    return $this->getQuery($keys, $state)
      ->count()
      ->execute();
  }

  /**
   * Get the base entity query filtered by form and search.
   *
   * @param string $keys
   *   (optional) Search key.
   * @param string $state
   *   (optional) Form state. Can be 'open' or 'closed'.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   An entity query.
   */
  protected function getQuery($keys = '', $state = '') {
    $query = $this->getStorage()->getQuery();

    // Filter by key(word).
    if ($keys) {
      $or = $query->orConditionGroup()
        ->condition('title', $this->keys, 'CONTAINS')
        ->condition('description', $this->keys, 'CONTAINS')
        ->condition('elements', $this->keys, 'CONTAINS');
      $query->condition($or);
    }

    // Filter by (form) state.
    if ($state == self::STATE_OPEN || $state == self::STATE_CLOSED) {
      $query->condition('status', ($state == self::STATE_OPEN) ? 1 : 0);
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $entity_ids = $this->getEntityIds();
    /* @var $entities \Drupal\yamlform\YamlFormInterface[] */
    $entities = $this->storage->loadMultiple($entity_ids);

    // If the user is not a form admin, check access to each form.
    if (!$this->isAdmin()) {
      foreach ($entities as $entity_id => $entity) {
        if (!$entity->access('update')) {
          unset($entities[$entity_id]);
        }
      }
    }

    return $entities;
  }

  /**
   * Get number of entities to list per page.
   *
   * @return int|false
   *   The number of entities to list per page, or FALSE to list all entities.
   */
  protected function getLimit() {
    return ($this->isAdmin()) ? $this->limit : FALSE;
  }

  /**
   * Is the current user a form administrator.
   *
   * @return bool
   *   TRUE if the current user has 'administer yamlform' or 'edit any yamlform'
   *   permission.
   */
  protected function isAdmin() {
    $account = \Drupal::currentUser();
    return ($account->hasPermission('administer yamlform') || $account->hasPermission('edit any yamlform') || $account->hasPermission('view any yamlform submission'));
  }

}
