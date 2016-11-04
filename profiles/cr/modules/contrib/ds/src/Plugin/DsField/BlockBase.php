<?php

namespace Drupal\ds\Plugin\DsField;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Render\Element;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Block\BlockManagerInterface;

/**
 * The base plugin to create DS block fields.
 */
abstract class BlockBase extends DsFieldBase implements ContainerFactoryPluginInterface {

  /**
   * The block.
   *
   * @var \Drupal\Core\Block\BlockPluginInterface
   */
  protected $block;

  /**
   * The BlockManager service.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * The context handler.
   *
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface
   */
  protected $contextHandler;

  /**
   * The context repository interface.
   *
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   */
  protected $contextRepository;

  /**
   * Constructs a Display Suite field plugin.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, ContextHandlerInterface $contextHandler, ContextRepositoryInterface $contextRepository, BlockManagerInterface $block_manager) {
    $this->blockManager = $block_manager;
    $this->contextHandler = $contextHandler;
    $this->contextRepository = $contextRepository;

    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('context.handler'),
      $container->get('context.repository'),
      $container->get('plugin.manager.block')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get block.
    $block = $this->getBlock();

    // Apply block config.
    $block_config = $this->blockConfig();
    $block->setConfiguration($block_config);

    if ($block->access(\Drupal::currentUser())) {
      // Inject context values.
      if ($block instanceof ContextAwarePluginInterface) {
        $contexts = $this->contextRepository->getRuntimeContexts(array_values($block->getContextMapping()));
        $this->contextHandler->applyContextMapping($block, $contexts);
      }

      $block_elements = $block->build();

      // Return an empty array if there is nothing to render.
      return Element::isEmpty($block_elements) ? [] : $block_elements;
    }

    return [];
  }

  /**
   * Returns the plugin ID of the block.
   */
  protected function blockPluginId() {
    return '';
  }

  /**
   * Returns the config of the block.
   */
  protected function blockConfig() {
    return array();
  }

  /**
   * Return the block entity.
   */
  protected function getBlock() {
    if (!$this->block) {
      // Create an instance of the block.
      /* @var $block BlockPluginInterface */
      $block_id = $this->blockPluginId();
      $block = $this->blockManager->createInstance($block_id);

      $this->block = $block;
    }

    return $this->block;
  }

}
