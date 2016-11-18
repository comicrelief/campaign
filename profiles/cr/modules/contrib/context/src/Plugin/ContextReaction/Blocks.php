<?php

namespace Drupal\context\Plugin\ContextReaction;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\Core\Form\FormState;
use Drupal\Core\Render\Element;
use Drupal\context\ContextInterface;
use Drupal\context\Form\AjaxFormTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\context\ContextReactionPluginBase;
use Drupal\Core\Block\TitleBlockPluginInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\context\Reaction\Blocks\BlockCollection;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Block\MainContentBlockPluginInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a content reaction that will let you place blocks in the current
 * themes regions.
 *
 * @ContextReaction(
 *   id = "blocks",
 *   label = @Translation("Blocks")
 * )
 */
class Blocks extends ContextReactionPluginBase implements ContainerFactoryPluginInterface {

  use AjaxFormTrait;

  /**
   * An array of blocks to be displayed with this reaction.
   *
   * @var array
   */
  protected $blocks = [];

  /**
   * Contains a temporary collection of blocks.
   *
   * @var BlockCollection
   */
  protected $blocksCollection;

  /**
   * The Drupal UUID service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuid;

  /**
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * @var ContextRepositoryInterface
   */
  protected $contextRepository;

  /**
   * @var ContextHandlerInterface
   */
  protected $contextHandler;

  /**
   * @var AccountInterface
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    UuidInterface $uuid,
    ThemeManagerInterface $themeManager,
    ThemeHandlerInterface $themeHandler,
    ContextRepositoryInterface $contextRepository,
    ContextHandlerInterface $contextHandler,
    AccountInterface $account
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);

    $this->uuid = $uuid;
    $this->themeManager = $themeManager;
    $this->themeHandler = $themeHandler;
    $this->contextRepository = $contextRepository;
    $this->contextHandler = $contextHandler;
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('uuid'),
      $container->get('theme.manager'),
      $container->get('theme_handler'),
      $container->get('context.repository'),
      $container->get('context.handler'),
      $container->get('current_user')
    );
  }

  /**
   * Executes the plugin.
   *
   * @param array $build
   *   The current build of the page.
   *
   * @param string|null $title
   *   The page title.
   *
   * @param string|null $main_content
   *   The main page content.
   *
   * @return array
   */
  public function execute(array $build = array(), $title = NULL, $main_content = NULL) {
    
    $cacheability = CacheableMetadata::createFromRenderArray($build);

    // Use the currently active theme to fetch blocks.
    $theme = $this->themeManager->getActiveTheme()->getName();

    $regions = $this->getBlocks()->getAllByRegion($theme);

    // Add each block to the page build.
    foreach ($regions as $region => $blocks) {

      /** @var $blocks BlockPluginInterface[] */
      foreach ($blocks as $block_id => $block) {
        $configuration = $block->getConfiguration();

        $block_placement_key = $this->blockShouldBePlacedUniquely($block)
          ? $block_id
          : $block->getConfiguration()['id'];

        if ($block instanceof MainContentBlockPluginInterface) {
          if (isset($build['content']['system_main'])) {
            unset($build['content']['system_main']);
          }
          $block->setMainContent($main_content);
        }

        // Make sure the user is allowed to view the block.
        $access = $block->access($this->account, TRUE);
        $cacheability->addCacheableDependency($access);

        // If the user is not allowed then do not render the block.
        if (!$access->isAllowed()) {
          continue;
        }

        if ($block instanceof TitleBlockPluginInterface) {
          if (isset($build['content']['messages'])) {
            unset($build['content']['messages']);
          }
          $block->setTitle($title);
        }

        // Inject runtime contexts.
        if ($block instanceof ContextAwarePluginInterface) {
          $contexts = $this->contextRepository->getRuntimeContexts($block->getContextMapping());
          $this->contextHandler->applyContextMapping($block, $contexts);
        }

        // Create the render array for the block as a whole.
        // @see template_preprocess_block().
        $blockBuild = [
          '#theme' => 'block',
          '#attributes' => [],
          '#configuration' => $configuration,
          '#plugin_id' => $block->getPluginId(),
          '#base_plugin_id' => $block->getBaseId(),
          '#derivative_plugin_id' => $block->getDerivativeId(),
          '#block_plugin' => $block,
          '#pre_render' => [[$this, 'preRenderBlock']],
          '#cache' => [
            'keys' => ['context_blocks_reaction', 'block', $block_placement_key, $block_placement_key],
            'tags' => $block->getCacheTags(),
            'contexts' => $block->getCacheContexts(),
            'max-age' => $block->getCacheMaxAge(),
          ],
        ];

        if (array_key_exists('weight', $configuration)) {
          $blockBuild['#weight'] = $configuration['weight'];
        }

        $build[$region][$block_placement_key] = $blockBuild;

        // The main content block cannot be cached: it is a placeholder for the
        // render array returned by the controller. It should be rendered as-is,
        // with other placed blocks "decorating" it. Analogous reasoning for the
        // title block.
        if ($block instanceof MainContentBlockPluginInterface || $block instanceof TitleBlockPluginInterface) {
          unset($build[$region][$block_placement_key]['#cache']['keys']);
        }

        $cacheability->addCacheableDependency($block);
      }
    }

    $cacheability->applyTo($build);

    return $build;
  }

  /**
   * Renders the content using the provided block plugin.
   *
   * @param  array $build
   * @return array
   */
  public function preRenderBlock($build) {

    $content = $build['#block_plugin']->build();

    unset($build['#block_plugin']);

    // Abort rendering: render as the empty string and ensure this block is
    // render cached, so we can avoid the work of having to repeatedly
    // determine whether the block is empty. E.g. modifying or adding entities
    // could cause the block to no longer be empty.
    if (is_null($content) || Element::isEmpty($content)) {
      $build = [
        '#markup' => '',
        '#cache' => $build['#cache'],
      ];

      // If $content is not empty, then it contains cacheability metadata, and
      // we must merge it with the existing cacheability metadata. This allows
      // blocks to be empty, yet still bubble cacheability metadata, to indicate
      // why they are empty.
      if (!empty($content)) {
        CacheableMetadata::createFromRenderArray($build)
          ->merge(CacheableMetadata::createFromRenderArray($content))
          ->applyTo($build);
      }
    }
    else {
      $build['content'] = $content;
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'blocks' => []
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration + $this->defaultConfiguration();

    if (isset($configuration['blocks'])) {
      $this->blocks = $configuration['blocks'];
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return [
      'blocks' => $this->getBlocks()->getConfiguration(),
    ] + parent::getConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Lets you add blocks to the selected themes regions');
  }

  /**
   * Get all blocks as a collection.
   *
   * @return BlockPluginInterface[]|BlockCollection
   */
  public function getBlocks() {
    if (!$this->blocksCollection) {
      $blockManager = \Drupal::service('plugin.manager.block');
      $this->blocksCollection = new BlockCollection($blockManager, $this->blocks);
    }

    return $this->blocksCollection;
  }

  /**
   * Get a block by id.
   *
   * @param string $blockId
   *   The ID of the block to get.
   *
   * @return BlockPluginInterface
   */
  public function getBlock($blockId) {
    return $this->getBlocks()->get($blockId);
  }

  /**
   * Add a new block.
   *
   * @param array $configuration
   */
  public function addBlock(array $configuration) {
    $configuration['uuid'] = $this->uuid->generate();

    $this->getBlocks()->addInstanceId($configuration['uuid'], $configuration);

    return $configuration['uuid'];
  }

  /**
   * Update an existing blocks configuration.
   *
   * @param string $blockId
   *   The ID of the block to update.
   *
   * @param $configuration
   *   The updated configuration for the block.
   *
   * @return $this
   */
  public function updateBlock($blockId, array $configuration) {
    $existingConfiguration = $this->getBlock($blockId)->getConfiguration();

    $this->getBlocks()->setInstanceConfiguration($blockId, $configuration + $existingConfiguration);

    return $this;
  }

  /**
   * @param $blockId
   * @return $this
   */
  public function removeBlock($blockId) {
    $this->getBlocks()->removeInstanceId($blockId);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, ContextInterface $context = NULL) {
    $form['#attached']['library'][] = 'block/drupal.block';

    $themes = $this->themeHandler->listInfo();

    $default_theme = $this->themeHandler->getDefault();

    // Select list for changing themes.
    $form['theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Theme'),
      '#options' => [],
      '#description' => $this->t('Select the theme you want to display regions for.'),
      '#default_value' => $form_state->getValue('theme', $default_theme),
      '#ajax' => [
        'url' => Url::fromRoute('context.reaction.blocks.regions', [
          'context' => $context->id(),
        ]),
      ],
    ];

    // Add each theme to the theme select.
    foreach ($themes as $theme_id => $theme) {
      if ($theme_id === $default_theme) {
        $form['theme']['#options'][$theme_id] = $this->t('%theme (Default)', [
          '%theme' => $theme->info['name'],
        ]);
      }
      else {
        $form['theme']['#options'][$theme_id] = $theme->info['name'];
      }
    }

    $form['blocks'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'context-reaction-blocks-container',
      ],
    ];

    $form['blocks']['block_add'] = [
      '#type' => 'link',
      '#title' => $this->t('Place block'),
      '#attributes' => [
          'id' => 'context-reaction-blocks-region-add',
        ] + $this->getAjaxButtonAttributes(),
      '#url' => Url::fromRoute('context.reaction.blocks.library', [
        'context' => $context->id(),
        'reaction_id' => $this->getPluginId(),
      ], [
        'query' => [
          'theme' => $form_state->getValue('theme', $default_theme),
        ],
      ]),
    ];

    $form['blocks']['blocks'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Block'),
        $this->t('Category'),
        $this->t('Unique'),
        $this->t('Region'),
        $this->t('Weight'),
        $this->t('Operations'),
      ],
      '#empty' => $this->t('No regions available to place blocks in.'),
      '#attributes' => [
        'id' => 'blocks',
      ],
    ];

    // If a theme has been selected use that to get the regions otherwise use
    // the default theme.
    $theme = $form_state->getValue('theme', $default_theme);

    // Get all blocks by their regions.
    $blocks = $this->getBlocks()->getAllByRegion($theme);

    // Get regions of the selected theme.
    $regions = $this->getSystemRegionList($theme);

    // Add each region.
    foreach ($regions as $region => $title) {

      // Add the tabledrag details for this region.
      $form['blocks']['blocks']['#tabledrag'][] = [
        'action' => 'match',
        'relationship' => 'sibling',
        'group' => 'block-region-select',
        'subgroup' => 'block-region-' . $region,
        'hidden' => FALSE,
      ];

      $form['blocks']['blocks']['#tabledrag'][] = [
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => 'block-weight',
        'subgroup' => 'block-weight-' . $region,
      ];

      // Add the theme region.
      $form['blocks']['blocks']['region-' . $region] = [
        '#attributes' => [
          'class' => ['region-title'],
        ],
        'title' => [
          '#markup' => $title,
          '#wrapper_attributes' => [
            'colspan' => 6,
          ],
        ],
      ];

      $regionEmptyClass = empty($blocks[$region])
        ? 'region-empty'
        : 'region-populated';

      $form['blocks']['blocks']['region-' . $region . '-message'] = [
        '#attributes' => [
          'class' => ['region-message', 'region-' . $region . '-message', $regionEmptyClass],
        ],
        'message' => [
          '#markup' => '<em>' . $this->t('No blocks in this region') . '</em>',
          '#wrapper_attributes' => [
            'colspan' => 6,
          ],
        ],
      ];

      // Add each block specified for the region if there are any.
      if (isset($blocks[$region])) {
        /** @var BlockPluginInterface $block */
        foreach ($blocks[$region] as $block_id => $block) {
          $configuration = $block->getConfiguration();

          $operations = [
            'edit' => [
              'title' => $this->t('Edit'),
              'url' => Url::fromRoute('context.reaction.blocks.block_edit', [
                'context' => $context->id(),
                'reaction_id' => $this->getPluginId(),
                'block_id' => $block_id,
              ], [
                'query' => [
                  'theme' => $theme,
                ],
              ]),
              'attributes' => $this->getAjaxAttributes(),
            ],
            'delete' => [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('context.reaction.blocks.block_delete', [
                'context' => $context->id(),
                'block_id' => $block_id,
              ]),
              'attributes' => $this->getAjaxAttributes(),
            ],
          ];

          $form['blocks']['blocks'][$block_id] = [
            '#attributes' => [
              'class' => ['draggable'],
            ],
            'label' => [
              '#markup' => $block->label(),
            ],
            'category' => [
              '#markup' => $block->getPluginDefinition()['category'],
            ],
            'unique' => [
              '#markup' => $this->blockShouldBePlacedUniquely($block) ? $this->t('Yes') : $this->t('No'),
            ],
            'region' => [
              '#type' => 'select',
              '#title' => $this->t('Region for @block block', ['@block' => $block->label()]),
              '#title_display' => 'invisible',
              '#default_value' => $region,
              '#options' => $regions,
              '#attributes' => [
                'class' => ['block-region-select', 'block-region-' . $region],
              ],
            ],
            'weight' => [
              '#type' => 'weight',
              '#default_value' => isset($configuration['weight']) ? $configuration['weight'] : 0,
              '#title' => $this->t('Weight for @block block', ['@block' => $block->label()]),
              '#title_display' => 'invisible',
              '#attributes' => [
                'class' => ['block-weight', 'block-weight-' . $region],
              ],
            ],
            'operations' => [
              '#type' => 'operations',
              '#links' => $operations,
            ],
          ];
        }
      }
    }

    return $form;
  }

  /**
   * Check to see if the block should be uniquely placed.
   *
   * @param BlockPluginInterface $block
   *
   * @return bool
   */
  private function blockShouldBePlacedUniquely(BlockPluginInterface $block) {
    $configuration = $block->getConfiguration();
    return (isset($configuration['unique']) && $configuration['unique']);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $blocks = $form_state->getValue(['blocks', 'blocks'], []);

    if (is_array($blocks)) {
      foreach ($blocks as $block_id => $configuration) {
        $block = $this->getBlock($block_id);
        $configuration += $block->getConfiguration();

        $block_state = (new FormState())->setValues($configuration);
        $block->submitConfigurationForm($form, $block_state);

        // If the block is context aware then add context mapping to the block.
        if ($block instanceof ContextAwarePluginInterface) {
          $block->setContextMapping($block_state->getValue('context_mapping', []));
        }

        $this->updateBlock($block_id, $block_state->getValues());
      }
    }
  }

  /**
   * Wraps system_region_list().
   *
   * @param string $theme
   *   The theme to get a list of regions for.
   *
   * @param string $show
   *   What type of regions that should be returned, defaults to all regions.
   *
   * @return array
   *
   * @todo This could be moved to a service since we use it in a couple of places.
   */
  protected function getSystemRegionList($theme, $show = REGIONS_ALL) {
    return system_region_list($theme, $show);
  }
}
