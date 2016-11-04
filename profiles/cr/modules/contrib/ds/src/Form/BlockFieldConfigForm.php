<?php

namespace Drupal\ds\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Plugin\ContextAwarePluginAssignmentTrait;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure block fields.
 */
class BlockFieldConfigForm extends FieldFormBase implements ContainerInjectionInterface {

  use ContextAwarePluginAssignmentTrait;

  /**
   * The context repository.
   *
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   */
  protected $contextRepository;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactory $config_factory, EntityTypeManagerInterface $entity_type_manager, CacheTagsInvalidatorInterface $cache_invalidator, ModuleHandler $module_handler, ContextRepositoryInterface $context_repository) {
    parent::__construct($config_factory, $entity_type_manager, $cache_invalidator, $module_handler);
    $this->contextRepository = $context_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('cache_tags.invalidator'),
      $container->get('module_handler'),
      $container->get('context.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $field_key = '') {
    // Fetch field.
    $field = $this->config('ds.field.' . $field_key)->get();

    // Save the field for future reuse.
    $this->field = $field;

    // Create an instance of the block.
    /* @var $block BlockPluginInterface */
    $manager = \Drupal::service('plugin.manager.block');
    $block_id = $field['properties']['block'];
    $block = $manager->createInstance($block_id);

    // Set block config form default values.
    if (isset($field['properties']['config'])) {
      $block->setConfiguration($field['properties']['config']);
    }

    // Get block config form.
    $form = $block->blockForm($form, $form_state);

    // If the block is context aware, attach the mapping widget.
    if ($block instanceof ContextAwarePluginInterface) {
      $form['context_mapping'] = $this->addContextAssignmentElement($block, $this->contextRepository->getAvailableContexts());
    }

    if (!$form) {
      return array('#markup' => $this->t("This block has no configuration options."));
    }

    // Some form items require this (core block manager also sets this).
    $form['#tree'] = TRUE;

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#weight' => 100,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $field = $this->field;

    // Create an instance of the block.
    /* @var $block BlockPluginInterface */
    $manager = \Drupal::service('plugin.manager.block');
    $block_id = $field['properties']['block'];
    $block = $manager->createInstance($block_id);

    // Validate block config data using the block's validation handler.
    $block->validateConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $field = $this->field;

    // Create an instance of the block.
    /* @var $block BlockPluginInterface */
    $manager = \Drupal::service('plugin.manager.block');
    $block_id = $field['properties']['block'];
    $block = $manager->createInstance($block_id);

    // Process block config data using the block's submit handler.
    $block->blockSubmit($form, $form_state);

    // If the block is context aware, store the context mapping.
    if ($block instanceof ContextAwarePluginInterface && $block->getContextDefinitions()) {
      $context_mapping = $form_state->getValue('context_mapping', []);
      $block->setContextMapping($context_mapping);
    }

    $block_config = $block->getConfiguration();

    // Clear cache tags.
    $this->cacheInvalidator->invalidateTags($block->getCacheTags());

    // Save block config.
    $this->config('ds.field.' . $field['id'])->set('properties.config', $block_config)->save();

    // Clear caches and redirect.
    $this->finishSubmitForm($form, $form_state);
  }

}
