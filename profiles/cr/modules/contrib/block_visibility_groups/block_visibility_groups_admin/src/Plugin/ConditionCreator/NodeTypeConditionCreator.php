<?php

namespace Drupal\block_visibility_groups_admin\Plugin\ConditionCreator;

use Drupal\block_visibility_groups_admin\Plugin\ConditionCreatorBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A condition creator to be used in creating user role condition.
 *
 * @ConditionCreator(
 *   id = "node_type",
 *   label = "Content Types",
 *   condition_plugin = "node_type"
 * )
 */
class NodeTypeConditionCreator extends ConditionCreatorBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface */
  protected $entityStorage;

  /**
   * NodeTypeConditionCreator constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\Core\Entity\EntityStorageInterface $entityStorage
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, EntityStorageInterface $entityStorage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityStorage = $entityStorage;

  }

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to pull out services used in the plugin.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   *
   * @return static Returns an instance of this plugin.
   *   Returns an instance of this plugin.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('node_type')
    );
  }

  /**
   *
   */
  public function getNewConditionLabel() {
    return $this->t('Content Types');
  }

  /**
   *
   */
  public function createConditionElements() {
    $elements['condition_config'] = [
      '#tree' => TRUE,
    ];
    if (empty($this->configuration['parameters']['node'])) {
      return [];
    }
    /** @var Node $node */
    $node = Node::load($this->configuration['parameters']['node']);
    $current_type = $node->getType();
    $node_types = $this->entityStorage->loadMultiple();
    $options = [];
    foreach ($node_types as $type) {
      $options[$type->id()] = $type->label();
    }
    $elements['condition_config']['bundles'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('When the user has the following bundles'),
      '#options' => $options,
      '#default_value' => [$current_type],
      // '#description' => $this->t('If you select no roles, the condition will evaluate to TRUE for all users.'),.
    );
    return $elements;
  }

  /**
   *
   */
  public function itemSelected($condition_info) {
    $bundles = $condition_info['condition_config']['bundles'];
    return !empty(array_filter($bundles));
  }

  /**
   *
   */
  public function createConditionConfig($plugin_info) {
    $config = parent::createConditionConfig($plugin_info);
    $config['bundles'] = array_filter($config['bundles']);
    $config['context_mapping'] = [
      'node' => '@node.node_route_context:node',
    ];
    return $config;
  }

}
