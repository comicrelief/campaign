<?php

namespace Drupal\context\Reaction\Blocks\Form;

use Drupal\context\ContextManager;
use Drupal\context\ContextReactionManager;
use Drupal\context\Form\AjaxFormTrait;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormState;
use Drupal\context\ContextInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class BlockFormBase extends FormBase {

  use AjaxFormTrait;

  /**
   * The plugin being configured.
   *
   * @var \Drupal\Core\Block\BlockPluginInterface
   */
  protected $block;

  /**
   * The context entity the reaction belongs to.
   *
   * @var ContextInterface
   */
  protected $context;

  /**
   * The blocks reaction this block should be added to.
   *
   * @var \Drupal\context\Plugin\ContextReaction\Blocks
   */
  protected $reaction;

  /**
   * The block manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $blockManager;

  /**
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   */
  protected $contextRepository;

  /**
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * @var \Drupal\context\ContextReactionManager
   */
  protected $contextReactionManager;

  /**
   * @var \Drupal\context\ContextManager
   */
  protected $contextManager;

  /**
   * Constructs a new VariantPluginFormBase.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $block_manager
   *   The block manager.
   *
   * @param \Drupal\Core\Plugin\Context\ContextRepositoryInterface $contextRepository
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $themeHandler
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   * @param \Drupal\context\ContextReactionManager $contextReactionManager
   * @param \Drupal\context\ContextManager $contextManager
   */
  public function __construct(
    PluginManagerInterface $block_manager,
    ContextRepositoryInterface $contextRepository,
    ThemeHandlerInterface $themeHandler,
    FormBuilderInterface $formBuilder,
    ContextReactionManager $contextReactionManager,
    ContextManager $contextManager
  )
  {
    $this->blockManager = $block_manager;
    $this->contextRepository = $contextRepository;
    $this->themeHandler = $themeHandler;
    $this->formBuilder = $formBuilder;
    $this->contextReactionManager = $contextReactionManager;
    $this->contextManager = $contextManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block'),
      $container->get('context.repository'),
      $container->get('theme_handler'),
      $container->get('form_builder'),
      $container->get('plugin.manager.context_reaction'),
      $container->get('context.manager')
    );
  }

  /**
   * Prepares the block plugin based on the block ID.
   *
   * @param string $block_id
   *   Either a block ID, or the plugin ID used to create a new block.
   *
   * @return \Drupal\Core\Block\BlockPluginInterface
   *   The block plugin.
   */
  abstract protected function prepareBlock($block_id);

  /**
   * Get the value to use for the submit button.
   *
   * @return TranslatableMarkup
   */
  abstract protected function getSubmitValue();

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @param ContextInterface $context
   *   The context the reaction belongs to.
   *
   * @param string|null $reaction_id
   *   The ID of the blocks reaction the block should be added to.
   *
   * @param string|null $block_id
   *   The ID of the block to show a configuration form for.
   *
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state, ContextInterface $context = NULL, $reaction_id = NULL, $block_id = NULL) {
    $this->context = $context;

    $this->reaction = $this->context->getReaction($reaction_id);
    $this->block = $this->prepareBlock($block_id);

    // If a theme was defined in the query use this theme for the block
    // otherwise use the default theme.
    $theme = $this->getRequest()->query->get('theme', $this->themeHandler->getDefault());

    // Some blocks require contexts, set a temporary value with gathered
    // contextual values.
    $form_state->setTemporaryValue('gathered_contexts', $this->contextRepository->getAvailableContexts());

    $configuration = $this->block->getConfiguration();

    $form['#tree'] = TRUE;

    $form['settings'] = $this->block->buildConfigurationForm([], $form_state);

    $form['settings']['id'] = [
      '#type' => 'value',
      '#value' => $this->block->getPluginId(),
    ];

    $form['region'] = [
      '#type' => 'select',
      '#title' => $this->t('Region'),
      '#description' => $this->t('Select the region where this block should be displayed.'),
      '#options' => $this->getThemeRegionOptions($theme),
      '#default_value' => isset($configuration['region']) ? $configuration['region'] : '',
    ];

    $form['unique'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Unique'),
      '#description' => $this->t('Check if the block should be uniquely placed, this means that the block can not be overridden by other blocks of the same type in the selected region.'),
      '#default_value' => isset($configuration['unique']) ? $configuration['unique'] : FALSE,
    ];

    $form['theme'] = [
      '#type' => 'value',
      '#value' => $theme,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->getSubmitValue(),
      '#button_type' => 'primary',
      '#ajax' => [
        'callback' => '::submitFormAjax'
      ],
    ];

    return $form;
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $settings = (new FormState())->setValues($form_state->getValue('settings'));

    // Call the plugin submit handler.
    $this->block->submitConfigurationForm($form, $settings);

    // Update the original form values.
    $form_state->setValue('settings', $settings->getValues());

    // Add available contexts if this is a context aware block.
    if ($this->block instanceof ContextAwarePluginInterface) {
      $this->block->setContextMapping($form_state->getValue(['settings', 'context_mapping'], []));
    }

    $configuration = array_merge($this->block->getConfiguration(), [
      'region' => $form_state->getValue('region'),
      'theme' => $form_state->getValue('theme'),
      'unique' => $form_state->getValue('unique'),
    ]);

    // Add/Update the block.
    if (!isset($configuration['uuid'])) {
      $this->reaction->addBlock($configuration);
    } else {
      $this->reaction->updateBlock($configuration['uuid'], $configuration);
    }

    $this->context->save();

    $form_state->setRedirectUrl(Url::fromRoute('entity.context.edit_form', [
      'context' => $this->context->id(),
    ]));
  }

  /**
   * Handle when the form is submitted trough AJAX.
   *
   * @return AjaxResponse
   */
  public function submitFormAjax() {
    $form = $this->contextManager->getForm($this->context, 'edit');

    $response = new AjaxResponse();

    $response->addCommand(new CloseModalDialogCommand());
    $response->addCommand(new ReplaceCommand('#context-reactions', $form['reactions']));

    return $response;
  }

  /**
   * Get a list of regions for the select list.
   *
   * @param string $theme
   *   The theme to get a list of regions for.
   *
   * @param string $show
   *   What type of regions that should be returned, defaults to all regions.
   *
   * @return array
   */
  protected function getThemeRegionOptions($theme, $show = REGIONS_ALL) {
    $regions = system_region_list($theme, $show);

    foreach ($regions as $region => $title) {
      $regions[$region] = $title;
    }

    return $regions;
  }

}

