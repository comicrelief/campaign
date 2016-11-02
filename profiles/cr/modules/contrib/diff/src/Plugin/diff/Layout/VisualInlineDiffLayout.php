<?php

namespace Drupal\diff\Plugin\diff\Layout;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\PhpStorage\PhpStorageFactory;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\diff\Controller\PluginRevisionController;
use Drupal\diff\DiffEntityComparison;
use Drupal\diff\DiffEntityParser;
use Drupal\diff\DiffLayoutBase;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use HtmlDiffAdvancedInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @DiffLayoutBuilder(
 *   id = "visual_inline",
 *   label = @Translation("Visual Inline"),
 *   description = @Translation("Visual layout, displays revision comparison using the entity type view mode."),
 * )
 */
class VisualInlineDiffLayout extends DiffLayoutBase {

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The diff entity comparison service.
   *
   * @var \Drupal\diff\DiffEntityComparison
   */
  protected $entityComparison;

  /**
   * The html diff service.
   *
   * @var \HtmlDiffAdvancedInterface
   */
  protected $htmlDiff;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * Constructs a VisualInlineDiffLayout object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The configuration factory object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\diff\DiffEntityParser $entity_parser
   *   The entity parser.
   * @param \Drupal\Core\DateTime\DateFormatter $date
   *   The date service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\diff\DiffEntityComparison $entity_comparison
   *   The diff entity comparison service.
   * @param \HtmlDiffAdvancedInterface $html_diff
   *   The html diff service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config, EntityTypeManagerInterface $entity_type_manager, DiffEntityParser $entity_parser, DateFormatter $date, RendererInterface $renderer, DiffEntityComparison $entity_comparison, HtmlDiffAdvancedInterface $html_diff, RequestStack $request_stack, EntityDisplayRepositoryInterface $entity_display_repository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $config, $entity_type_manager, $entity_parser, $date);
    $this->renderer = $renderer;
    $this->entityComparison = $entity_comparison;
    $storage = PhpStorageFactory::get('html_purifier_serializer');
    if (!$storage->exists('cache.php')) {
      $storage->save('cache.php', 'dummy');
    }
    $html_diff->getConfig()->setPurifierCacheLocation(dirname($storage->getFullPath('cache.php')));
    $this->htmlDiff = $html_diff;
    $this->requestStack = $request_stack;
    $this->entityDisplayRepository = $entity_display_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('diff.entity_parser'),
      $container->get('date.formatter'),
      $container->get('renderer'),
      $container->get('diff.entity_comparison'),
      $container->get('diff.html_diff'),
      $container->get('request_stack'),
      $container->get('entity_display.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(EntityInterface $left_revision, EntityInterface $right_revision, EntityInterface $entity) {
    $this->entityTypeManager->getStorage($entity->getEntityTypeId())->resetCache([$entity->id()]);

    // Show the revisions that are compared.
    $build['diff_revisions'] = [
      '#type' => 'item',
      '#title' => $this->t('Comparing'),
      '#weight' => 0,
    ];
    $build['diff_revisions']['left_revision'] = $this->buildRevisionLinkVisualInline($left_revision);
    $build['diff_revisions']['left_revision']['#prefix'] = '<div class="comparation-flex-container">';
    $build['diff_revisions']['left_revision']['#suffix'] = '</div>';
    $build['diff_revisions']['right_revision'] = $this->buildRevisionLinkVisualInline($right_revision);
    $build['diff_revisions']['right_revision']['#prefix'] = '<div class="comparation-flex-container">';
    $build['diff_revisions']['right_revision']['#suffix'] = '</div>';

    // Build the view modes filter.
    $options = [];
    // Get all view modes for entity type.
    $view_modes = $this->entityDisplayRepository->getViewModeOptionsByBundle($entity->getEntityTypeId(), $entity->bundle());
    foreach ($view_modes as $view_mode => $view_mode_info) {
      // Skip view modes that are not used in the front end.
      if (in_array($view_mode, ['rss', 'search_index'])) {
        continue;
      }
      $options[$view_mode] = [
        'title' => $view_mode_info,
        'url' => PluginRevisionController::diffRoute($entity,
          $left_revision->getRevisionId(),
          $right_revision->getRevisionId(),
          'visual_inline',
          ['view_mode' => $view_mode]
        ),
      ];
    }

    $active_option = array_keys($options);
    $active_view_mode = $this->requestStack->getCurrentRequest()->query->get('view_mode') ?: reset($active_option);

    $filter = $options[$active_view_mode];
    unset($options[$active_view_mode]);
    array_unshift($options, $filter);

    $build['view_mode'] = [
      '#type' => 'item',
      '#title' => $this->t('View mode'),
      '#weight' => 3,
      '#prefix' => '<div class="diff-layout">',
      '#suffix' => '</div>',
    ];
    $build['view_mode']['filter'] = [
      '#type' => 'operations',
      '#links' => $options,
      '#prefix' => '<div class="diff-filter">',
      '#suffix' => '</div>',
    ];

    $view_builder = $this->entityTypeManager->getViewBuilder($entity->getEntityTypeId());
    // Trigger exclusion of interactive items like on preview.
    $left_revision->in_preview = TRUE;
    $right_revision->in_preview = TRUE;
    $left_view = $view_builder->view($left_revision, $active_view_mode);
    $right_view = $view_builder->view($right_revision, $active_view_mode);

    // Avoid render cache from being built.
    unset($left_view['#cache']);
    unset($right_view['#cache']);

    $html_1= $this->renderer->render($left_view);
    $html_2 = $this->renderer->render($right_view);

    $this->htmlDiff->setOldHtml($html_1);
    $this->htmlDiff->setNewHtml($html_2);
    $this->htmlDiff->build();

    $build['diff'] = [
      '#markup' => $this->htmlDiff->getDifference(),
      '#weight' => 10,
    ];

    $build['#attached']['library'][] = 'diff/diff.visual_inline';
    return $build;
  }

  /**
   * Build the revision link for visual inline layout.
   *
   * @param \Drupal\Core\Entity\EntityInterface $revision
   *   A revision where to add a link.
   *
   * @return \Drupal\Core\GeneratedLink
   *   Header link for a revision comparision.
   */
  protected function buildRevisionLinkVisualInline(EntityInterface $revision) {
    $entity_type_id = $revision->getEntityTypeId();
    if ($revision instanceof EntityRevisionLogInterface || $revision instanceof NodeInterface) {
      $revision_log = '';

      if ($revision instanceof EntityRevisionLogInterface) {
        $revision_log = Xss::filter($revision->getRevisionLogMessage());
      }
      elseif ($revision instanceof NodeInterface) {
        $revision_log = $revision->revision_log->value;
      }
      $user_id = $revision->getRevisionUserId();
      $route_name = $entity_type_id != 'node' ? "entity.$entity_type_id.revisions_diff" : 'entity.node.revision';

      $revision_link['date'] = [
        '#type' => 'link',
        '#title' => $this->date->format($revision->getRevisionCreationTime(), 'short'),
        '#url' => Url::fromRoute($route_name, [
          $entity_type_id => $revision->id(),
          $entity_type_id . '_revision' => $revision->getRevisionId(),
        ]),
        '#prefix' => '<div class="comparation-flex-item-date">',
        '#suffix' => '</div>',
      ];

      $revision_link['author'] = [
        '#type' => 'link',
        '#title' => $revision->getRevisionUser()->getDisplayName(),
        '#url' => Url::fromUri(\Drupal::request()->getUriForPath('/user/' . $user_id)),
        '#theme' => 'username',
        '#account' => $revision->getRevisionUser(),
        '#prefix' => '<div class="comparation-flex-item-author">',
        '#suffix' => '</div>',
      ];

      if ($revision_log) {
        $revision_link['message'] = [
          '#type' => 'markup',
          '#prefix' => '<div class="comparation-flex-item-message">',
          '#suffix' => '</div>',
          '#markup' => $revision_log,
        ];
      }
    }
    else {
      $revision_link = Link::fromTextAndUrl($revision->label(), $revision->toUrl('revision'))->toString();
    }
    return $revision_link;
  }

}
