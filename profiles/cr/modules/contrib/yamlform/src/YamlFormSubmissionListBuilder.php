<?php

namespace Drupal\yamlform;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\AlterableInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\yamlform\Utility\YamlFormDialogHelper;

/**
 * Provides a list controller for yamlform submission entity.
 *
 * @ingroup yamlform
 */
class YamlFormSubmissionListBuilder extends EntityListBuilder {

  /**
   * Submission state starred.
   */
  const STATE_STARRED = 'starred';

  /**
   * Submission state unstarred.
   */
  const STATE_UNSTARRED = 'unstarred';

  /**
   * The form request handler.
   *
   * @var \Drupal\yamlform\YamlFormRequestInterface
   */
  protected $requestHandler;

  /**
   * The form.
   *
   * @var \Drupal\yamlform\YamlFormInterface
   */
  protected $yamlform;

  /**
   * The entity that a form is attached to. Currently only applies to nodes.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $sourceEntity;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The columns being displayed.
   *
   * @var array
   */
  protected $columns;

  /**
   * The table's header.
   *
   * @var array
   */
  protected $header;

  /**
   * The table's header and element format settings.
   *
   * @var array
   */
  protected $format = [
    'header_format' => 'label',
    'element_format' => 'value',
  ];

  /**
   * The form elements.
   *
   * @var array
   */
  protected $elements;

  /**
   * Search keys.
   *
   * @var string
   */
  protected $keys;

  /**
   * Sort by.
   *
   * @var string
   */
  protected $sort;

  /**
   * Sort direction.
   *
   * @var string
   */
  protected $direction;

  /**
   * Search state.
   *
   * @var string
   */
  protected $state;

  /**
   * Track if table can be customized..
   *
   * @var bool
   */
  protected $customize;

  /**
   * The form element manager.
   *
   * @var \Drupal\yamlform\YamlFormElementManagerInterface
   */
  protected $elementManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage) {
    parent::__construct($entity_type, $storage);

    $this->requestHandler = \Drupal::service('yamlform.request');

    $this->keys = \Drupal::request()->query->get('search');
    $this->state = \Drupal::request()->query->get('state');

    list($this->yamlform, $this->sourceEntity) = $this->requestHandler->getYamlFormEntities();

    $base_route_name = ($this->yamlform) ? $this->requestHandler->getBaseRouteName($this->yamlform, $this->sourceEntity) : '';

    $this->account = (\Drupal::routeMatch()->getRouteName() == "$base_route_name.yamlform.user.submissions") ? \Drupal::currentUser() : NULL;

    $this->elementManager = \Drupal::service('plugin.manager.yamlform.element');

    /** @var YamlFormSubmissionStorageInterface $yamlform_submission_storage */
    $yamlform_submission_storage = $this->getStorage();

    if (\Drupal::routeMatch()->getRouteName() == "$base_route_name.yamlform.results_table") {
      $this->columns = $yamlform_submission_storage->getCustomColumns($this->yamlform, $this->sourceEntity, $this->account, TRUE);
      $this->sort = $yamlform_submission_storage->getCustomSetting('sort', 'serial', $this->yamlform, $this->sourceEntity);
      $this->direction  = $yamlform_submission_storage->getCustomSetting('direction', 'desc', $this->yamlform, $this->sourceEntity);
      $this->limit = $yamlform_submission_storage->getCustomSetting('limit', 50, $this->yamlform, $this->sourceEntity);
      $this->format = $yamlform_submission_storage->getCustomSetting('format', $this->format, $this->yamlform, $this->sourceEntity);
      $this->customize = TRUE;
      if ($this->format['element_format'] == 'raw') {
        foreach ($this->columns as &$column) {
          $column['format'] = 'raw';
          if (isset($column['element'])) {
            $column['element']['#format'] = 'raw';
          }
        }
      }
    }
    else {
      $this->columns = $yamlform_submission_storage->getDefaultColumns($this->yamlform, $this->sourceEntity, $this->account, FALSE);
      $this->sort = 'serial';
      $this->direction  = 'desc';
      $this->limit = 50;
      $this->customize = FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    if ($this->yamlform) {
      if ($this->account) {
        $build['#title'] = $this->t('Submissions to %yamlform for %user', [
          '%yamlform' => $this->yamlform->label(),
          '%user' => $this->account->getDisplayName(),
        ]);
      }
    }

    // Add the filter.
    if (empty($this->account)) {
      $state_options = [
        '' => $this->t('All [@total]', ['@total' => $this->getTotal(NULL, NULL)]),
        'starred' => $this->t('Starred [@total]', ['@total' => $this->getTotal(NULL, self::STATE_STARRED)]),
        'unstarred' => $this->t('Unstarred [@total]', ['@total' => $this->getTotal(NULL, self::STATE_UNSTARRED)]),
      ];
      $build['filter_form'] = \Drupal::formBuilder()
        ->getForm('\Drupal\yamlform\Form\YamlFormSubmissionFilterForm', $this->keys, $this->state, $state_options);
    }

    // Customize.
    if ($this->customize) {
      $route_name = $this->requestHandler->getRouteName($this->yamlform, $this->sourceEntity, 'yamlform.results_table.custom');
      $route_parameters = $this->requestHandler->getRouteParameters($this->yamlform, $this->sourceEntity) + ['yamlform' => $this->yamlform->id()];
      $route_options = ['query' => \Drupal::destination()->getAsArray()];
      $build['custom'] = [
        '#type' => 'link',
        '#title' => $this->t('Customize'),
        '#url' => Url::fromRoute($route_name, $route_parameters, $route_options),
        '#attributes' => YamlFormDialogHelper::getModalDialogAttributes(800, ['button', 'button-action', 'button--small', 'button-yamlform-setting']),
      ];
    }

    $build += parent::render();

    $build['table']['#attributes']['class'][] = 'yamlform-results__table';

    $build['#attached']['library'][] = 'yamlform/yamlform.admin';

    return $build;
  }

  /****************************************************************************/
  // Header functions.
  /****************************************************************************/

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    if (isset($this->header)) {
      return $this->header;
    }

    $responsive_priorities = [
      'created' => RESPONSIVE_PRIORITY_MEDIUM,
      'langcode' => RESPONSIVE_PRIORITY_LOW,
      'remote_addr' => RESPONSIVE_PRIORITY_LOW,
      'uid' => RESPONSIVE_PRIORITY_MEDIUM,
      'yamlform' => RESPONSIVE_PRIORITY_LOW,
    ];

    $header = [];
    foreach ($this->columns as $column_name => $column) {
      $header[$column_name] = $this->buildHeaderColumn($column);

      // Apply custom sorting to header.
      if ($column_name === $this->sort) {
        $header[$column_name]['sort'] = $this->direction;
      }

      // Apply responsive priorities to header.
      if (isset($responsive_priorities[$column_name])) {
        $header[$column_name]['class'][] = $responsive_priorities[$column_name];
      }
    }
    $this->header = $header;
    return $this->header;
  }

  /**
   * Build table header column.
   *
   * @param array $column
   *   The column.
   *
   * @return array
   *   A renderable array containing a table header column.
   *
   * @throws \Exception
   *   Throw exception if table header column is not found.
   */
  protected function buildHeaderColumn(array $column) {
    $name = $column['name'];
    if ($this->format['header_format'] == 'key') {
      $title = isset($column['key']) ? $column['key'] : $column['name'];
    }
    else {
      $title = $column['title'];
    }

    switch ($name) {
      case 'notes':
      case 'sticky':
        return [
          'data' => new FormattableMarkup('<span class="yamlform-icon yamlform-icon-@name yamlform-icon-@name--link"></span>', ['@name' => $name]),
          'class' => ['yamlform-results__icon'],
          'field' => 'sticky',
          'specifier' => 'sticky',
        ];

      default:
        if (isset($column['sort']) && $column['sort'] === FALSE) {
          return ['data' => $title];
        }
        else {
          return [
            'data' => $title,
            'field' => $name,
            'specifier' => $name,
          ];
        }
    }
  }

  /****************************************************************************/
  // Row functions.
  /****************************************************************************/

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $route_name = $this->requestHandler->getRouteName($entity, $this->sourceEntity, $this->getSubmissionRouteName());
    $route_parameters = $this->requestHandler->getRouteParameters($entity, $this->sourceEntity);

    $row = [
      'data' => [],
      'data-yamlform-href' => Url::fromRoute($route_name, $route_parameters)->toString(),
    ];
    foreach ($this->columns as $column_name => $column) {
      $row['data'][$column_name] = $this->buildRowColumn($column, $entity);
    }

    return $row;
  }

  /**
   * Build row column.
   *
   * @param array $column
   *   Column settings.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   A form submission.
   *
   * @return array|mixed
   *   The row column value or renderable array.
   *
   * @throws \Exception
   *   Throw exception if table row column is not found.
   */
  public function buildRowColumn(array $column, EntityInterface $entity) {
    $is_raw = ($column['format'] == 'raw');
    $name = $column['name'];

    switch ($name) {
      case 'created':
      case 'completed':
      case 'changed':
        return ($is_raw) ? $entity->created->value : \Drupal::service('date.formatter')->format($entity->created->value);

      case 'entity':
        $source_entity = $entity->getSourceEntity();
        if (!$source_entity) {
          return '';
        }
        return ($is_raw) ? $source_entity->getEntityTypeId . ':' . $source_entity->id() : $source_entity->toLink();

      case 'langcode':
        return ($is_raw) ? $entity->langcode->value : \Drupal::languageManager()->getLanguage($entity->langcode->value)->getName();

      case 'notes':
        $route_name = $this->requestHandler->getRouteName($entity, $entity->getSourceEntity(), 'yamlform_submission.notes_form');
        $route_parameters = $this->requestHandler->getRouteParameters($entity, $entity->getSourceEntity());
        $route_options = ['query' => \Drupal::destination()->getAsArray()];
        $state = $entity->get('notes')->value ? 'on' : 'off';
        return [
          'data' => [
            '#type' => 'link',
            '#title' => new FormattableMarkup('<span class="yamlform-icon yamlform-icon-notes yamlform-icon-notes--@state"></span>', ['@state' => $state]),
            '#url' => Url::fromRoute($route_name, $route_parameters, $route_options),
            '#attributes' => YamlFormDialogHelper::getModalDialogAttributes(400),
          ],
          'class' => ['yamlform-results__icon'],
        ];

      case 'operations':
        return ['data' => $this->buildOperations($entity)];

      case 'remote_addr':
        return $entity->getRemoteAddr();

      case 'sid':
        return $entity->id();

      case 'serial':
        $route_name = $this->requestHandler->getRouteName($entity, $this->sourceEntity, $this->getSubmissionRouteName());
        $route_parameters = $this->requestHandler->getRouteParameters($entity, $this->sourceEntity);
        $link_text = $entity->serial() . ($entity->isDraft() ? ' (' . $this->t('draft') . ')' : '');
        return Link::createFromRoute($link_text, $route_name, $route_parameters);

      case 'sticky':
        $route_name = 'entity.yamlform_submission.sticky_toggle';
        $route_parameters = ['yamlform' => $entity->getYamlForm()->id(), 'yamlform_submission' => $entity->id()];
        $state = $entity->isSticky() ? 'on' : 'off';
        return [
          'data' => [
            '#type' => 'link',
            '#title' => new FormattableMarkup('<span class="yamlform-icon yamlform-icon-sticky yamlform-icon-sticky--@state"></span>', ['@state' => $state]),
            '#url' => Url::fromRoute($route_name, $route_parameters),
            '#attributes' => [
              'id' => 'yamlform-submission-' . $entity->id() . '-sticky',
              'class' => ['use-ajax'],
            ],
          ],
          'class' => ['yamlform-results__icon'],
        ];

      case 'uid':
        return ($is_raw) ? $entity->getOwner()->id() : ($entity->getOwner()->getAccountName() ?: t('Anonymous'));

      case 'uuid':
        return $entity->uuid();

      case 'yamlform_id':
        return ($is_raw) ? $entity->getYamlForm()->id() : $entity->getYamlForm()->toLink();

      default:
        if (strpos($name, 'element__') === 0) {
          $data = $entity->getData();

          $element = $column['element'];

          $key = $column['key'];
          $value  = (isset($data[$key])) ? $data[$key] : '';

          $options = $column;

          /** @var \Drupal\yamlform\YamlFormElementInterface $element_handler */
          $element_handler = $column['plugin'];
          $html = $element_handler->formatTableColumn($element, $value, $options);
          return (is_array($html)) ? ['data' => $html] : $html;
        }
        else {
          return '';
        }
    }
  }

  /****************************************************************************/
  // Operations.
  /****************************************************************************/

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $base_route_name = $this->requestHandler->getBaseRouteName($entity, $this->sourceEntity);
    $route_parameters = $this->requestHandler->getRouteParameters($entity, $this->sourceEntity);
    $route_options = ['query' => \Drupal::destination()->getAsArray()];

    $operations = [];

    if ($entity->access('update')) {
      $operations['edit'] = [
        'title' => $this->t('Edit'),
        'weight' => 10,
        'url' => Url::fromRoute("$base_route_name.yamlform_submission.edit_form", $route_parameters, $route_options),
      ];
    }

    if ($entity->access('view')) {
      $operations['view'] = [
        'title' => $this->t('View'),
        'weight' => 20,
        'url' => Url::fromRoute("$base_route_name.yamlform_submission.canonical", $route_parameters),
      ];
    }

    if ($entity->access('update')) {
      $operations['notes'] = [
        'title' => $this->t('Notes'),
        'weight' => 21,
        'url' => Url::fromRoute("$base_route_name.yamlform_submission.notes_form", $route_parameters, $route_options),
      ];
      $operations['resend'] = [
        'title' => $this->t('Resend'),
        'weight' => 22,
        'url' => Url::fromRoute("$base_route_name.yamlform_submission.resend_form", $route_parameters, $route_options),
      ];
    }

    if ($entity->access('delete')) {
      $operations['delete'] = [
        'title' => $this->t('Delete'),
        'weight' => 100,
        'url' => Url::fromRoute("$base_route_name.yamlform_submission.delete_form", $route_parameters, $route_options),
      ];
    }

    return $operations;
  }

  /****************************************************************************/
  // Route functions.
  /****************************************************************************/

  /**
   * Get submission route name based on the current route.
   *
   * @return string
   *   The submission route name which can be either 'yamlform.user.submission'
   *   or 'yamlform_submission.canonical.
   */
  protected function getSubmissionRouteName() {
    return (strpos(\Drupal::routeMatch()->getRouteName(), 'yamlform.user.submissions') !== FALSE) ? 'yamlform.user.submission' : 'yamlform_submission.canonical';
  }

  /**
   * Get base route name for the form or form source entity.
   *
   * @return string
   *   The base route name for form or form source entity.
   */
  protected function getBaseRouteName() {
    return $this->requestHandler->getBaseRouteName($this->yamlform, $this->sourceEntity);
  }

  /**
   * Get route parameters for the form or form source entity.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A form submission.
   *
   * @return array
   *   Route parameters for the form or form source entity.
   */
  protected function getRouteParameters(YamlFormSubmissionInterface $yamlform_submission) {
    $route_parameters = ['yamlform_submission' => $yamlform_submission->id()];
    if ($this->sourceEntity) {
      $route_parameters[$this->sourceEntity->getEntityTypeId()] = $this->sourceEntity->id();
    }
    return $route_parameters;
  }

  /****************************************************************************/
  // Query functions.
  /****************************************************************************/

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    $query = $this->getQuery($this->keys, $this->state);
    $query->pager($this->limit);

    $header = $this->buildHeader();
    $order = tablesort_get_order($header);
    $direction = tablesort_get_sort($header);

    // If query is order(ed) by 'element__*' we need to build a custom table
    // sort using hook_query_alter().
    // @see: yamlform_query_alter()
    if ($order && strpos($order['sql'], 'element__') === 0) {
      $name = $order['sql'];
      $column = $this->columns[$name];
      $query->addMetaData('yamlform_submission_element_name', $column['key']);
      $query->addMetaData('yamlform_submission_element_property_name', $column['property_name']);
      $query->addMetaData('yamlform_submission_element_direction', $direction);
    }
    else {
      $query->tableSort($header);
    }

    return $query->execute();
  }

  /**
   * Get the total number of submissions.
   *
   * @param string $keys
   *   (optional) Search key.
   * @param string $state
   *   (optional) Submission state. Can be 'starred' or 'unstarred'.
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
   *   (optional) Submission state. Can be 'starred' or 'unstarred'.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   An entity query.
   */
  protected function getQuery($keys = '', $state = '') {
    $query = $this->getStorage()->getQuery();
    $this->addQueryConditions($query);

    // Filter by key(word).
    if ($keys) {
      $sub_query = Database::getConnection()->select('yamlform_submission_data', 'sd')
        ->fields('sd', ['sid'])
        ->condition('value', '%' . $keys . '%', 'LIKE');
      $this->addQueryConditions($sub_query);

      $or = $query->orConditionGroup()
        ->condition('sid', $sub_query, 'IN')
        ->condition('notes', '%' . $keys . '%', 'LIKE');

      $query->condition($or);
    }

    // Filter by (submission) state.
    if ($state == self::STATE_STARRED || $state == self::STATE_UNSTARRED) {
      $query->condition('sticky', ($state == self::STATE_STARRED) ? 1 : 0);
    }

    return $query;
  }

  /**
   * Add form, account, and source entity conditions to a query.
   *
   * @param \Drupal\Core\Database\Query\AlterableInterface $query
   *   The query to execute.
   */
  protected function addQueryConditions(AlterableInterface $query) {
    // Limit submission to the current form.
    if ($this->yamlform) {
      $query->condition('yamlform_id', $this->yamlform->id());
    }

    // Limit submission to the current user.
    if ($this->account) {
      $query->condition('uid', $this->account->id());
    }

    // Filter entity type and id. (Currently only applies to yamlform_node.module)
    if ($this->sourceEntity) {
      $query->condition('entity_type', $this->sourceEntity->getEntityTypeId());
      $query->condition('entity_id', $this->sourceEntity->id());
    }
  }

}
