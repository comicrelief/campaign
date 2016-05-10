<?php

/**
 * @file
 * Contains \Drupal\yamlform\Entity\YamlFormSubmissionListBuilder.
 */

namespace Drupal\yamlform;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Url;

/**
 * Provides a list controller for yamlform submission entity.
 *
 * @ingroup yamlform
 */
class YamlFormSubmissionListBuilder extends EntityListBuilder {

  /**
   * The YAML form.
   *
   * @var \Drupal\yamlform\Entity\YamlForm
   */
  protected $yamlform;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The table header columns.
   *
   * @var array
   */
  protected $header;

  /**
   * The YAML form elements.
   *
   * @var array
   */
  protected $elements = [];

  /**
   * The YAML form results filter search keys.
   *
   * @var string
   */
  protected $keys;

  /**
   * The YAMl form element manager.
   *
   * @var \Drupal\yamlform\YamlFormElementManager
   */
  protected $elementManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage) {
    parent::__construct($entity_type, $storage);
    $this->keys = \Drupal::request()->query->get('search');
    $this->yamlform = \Drupal::routeMatch()->getParameter('yamlform');
    $this->account = (\Drupal::routeMatch()->getRouteName() == 'entity.yamlform.submissions') ? \Drupal::currentUser() : FALSE;
    if ($this->yamlform && \Drupal::routeMatch()->getRouteName() == 'entity.yamlform.results_table') {
      $this->elements = $this->yamlform->getElements();
      // Use the default format when displaying each element.
      foreach ($this->elements as &$element) {
        unset($element['#format']);
      }
    }
    $this->elementManager = \Drupal::service('plugin.manager.yamlform.element');
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
      else {
        $build['#title'] = $this->yamlform->label();
      }
    }

    // Add the filter.
    $build['filter_form'] = \Drupal::formBuilder()->getForm('\Drupal\yamlform\Form\YamlFormFilterForm', $this->t('submissions'), $this->t('Filter by submitted data'), $this->keys);

    // Display info.
    if ($total = $this->getTotal()) {
      $t_args = [
        '@total' => $total,
        '@results' => $this->formatPlural($total, $this->t('submission'), $this->t('submissions')),
      ];
      $build['info'] = [
        '#markup' => $this->t('@total @results', $t_args),
        '#prefix' => '<div>',
        '#suffix' => '</div>',
      ];
    }

    $build += parent::render();

    $build['table']['#attributes']['class'][] = 'yamlform-results';

    $build['#attached']['library'][] = 'yamlform/yamlform';

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    if (isset($this->header)) {
      return $this->header;
    }

    $view_any = ($this->yamlform && $this->yamlform->access('submission_view_any')) ? TRUE : FALSE;

    $header['sid'] = [
      'data' => $this->t('#'),
      'field' => 'sid',
      'specifier' => 'sid',
      'sort' => 'desc',
    ];

    $header['created'] = [
      'data' => $this->t('Submitted'),
      'field' => 'created',
      'specifier' => 'created',
      'class' => [RESPONSIVE_PRIORITY_MEDIUM],
    ];

    if ($view_any) {
      $header['entity'] = [
        'data' => $this->t('Submitted to'),
      ];
    }

    if (empty($this->account)) {
      $header['uid'] = [
        'data' => $this->t('User'),
        'field' => 'uid',
        'specifier' => 'uid',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ];
    }

    if ($view_any && $this->moduleHandler()->moduleExists('language')) {
      $header['langcode'] = [
        'data' => $this->t('Language'),
        'field' => 'langcode',
        'specifier' => 'langcode',
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ];
    }

    $header['remote_addr'] = [
      'data' => $this->t('IP address'),
      'field' => 'remote_addr',
      'specifier' => 'remote_addr',
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];

    if (empty($this->yamlform)) {
      $header['yamlform'] = [
        'data' => $this->t('Form'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ];
    }

    if ($this->elements) {
      foreach ($this->elements as $key => $element) {
        $header['input_' . $key] = $element['#title'] ?: $key;
      }
    }

    // Cache header in protected variable.
    $this->header = $header + parent::buildHeader();
    return $this->header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\yamlform\YamlFormSubmissionInterface */
    $view_any = ($this->yamlform && $this->yamlform->access('submission_view_any')) ? TRUE : FALSE;

    $row['sid'] = $entity->toLink($entity->id() . ($entity->isDraft() ? ' (' . $this->t('draft') . ')' : ''));

    $row['created'] = \Drupal::service('date.formatter')->format($entity->created->value);

    if ($view_any) {
      $row['entity'] = ($source_entity = $entity->getSourceEntity()) ? $source_entity->toLink() : '';
    }

    if (empty($this->account)) {
      $row['user'] = $entity->getOwner()->getAccountName() ?: t('Anonymous');
    }

    if ($view_any && $this->moduleHandler()->moduleExists('language')) {
      $row['langcode'] = \Drupal::languageManager()->getLanguage($entity->langcode->value)->getName();
    }

    $row['remote_addr'] = $entity->remote_addr->value;

    if (empty($this->yamlform)) {
      $row['yamlform'] = $entity->getYamlForm()->toLink();
    }

    if ($this->elements) {
      $data = $entity->getData();
      foreach ($this->elements as $key => $element) {
        $options = [];
        $html = $this->elementManager->invokeMethod('formatHtml', $element, $data[$key], $options);
        if (is_array($html)) {
          $row['input_' . $key] = ['data' => $html];
        }
        else {
          $row['input_' . $key] = $html;
        }
      }
    }

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    // Add destination to edit and delete operations.
    foreach ($operations as &$operation) {
      $operation['url']->setOptions(['query' => \Drupal::destination()->getAsArray()]);
    }

    // Add view and resend to default operations.
    $operations['view'] = [
      'title' => $this->t('View'),
      'weight' => 20,
      'url' => Url::fromRoute('entity.yamlform_submission.canonical', ['yamlform_submission' => $entity->id()]),
    ];

    $operations['resend'] = [
      'title' => $this->t('Resend'),
      'weight' => 21,
      'url' => Url::fromRoute('entity.yamlform_submission.resend_form', ['yamlform_submission' => $entity->id()]),
    ];

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    $header = $this->buildHeader();
    return $this->getQuery()
      ->pager($this->limit)
      ->tableSort($header)
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

    // Limit submission to the current YAML form.
    if ($this->yamlform) {
      $query->condition('yamlform_id', $this->yamlform->id());
    }

    // Limit submission to the current user.
    if ($this->account) {
      $query->condition('uid', $this->account->id());
    }

    // Filter submissions.
    if ($this->keys) {
      $query->condition('data', '%' . $this->keys . '%', 'LIKE');
    }

    return $query;
  }

}
