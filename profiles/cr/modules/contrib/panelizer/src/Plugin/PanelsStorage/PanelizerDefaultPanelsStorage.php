<?php

/**
 * @file
 * Contains \Drupal\panelizer\PanelizerDefaultPanelsStorage
 */

namespace Drupal\panelizer\Plugin\PanelsStorage;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Session\AccountInterface;
use Drupal\panelizer\PanelizerInterface;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;
use Drupal\panels\Storage\PanelsStorageBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Panels storage service that stores Panels displays in Panelizer defaults.
 *
 * @PanelsStorage("panelizer_default")
 */
class PanelizerDefaultPanelsStorage extends PanelsStorageBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\panelizer\PanelizerInterface
   */
  protected $panelizer;

  /**
   * Constructs a PanelizerDefaultPanelsStorage.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\panelizer\PanelizerInterface $panelizer
   *   The Panelizer service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, PanelizerInterface $panelizer) {
    $this->entityTypeManager = $entity_type_manager;
    $this->panelizer = $panelizer;
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
      $container->get('entity_type.manager'),
      $container->get('panelizer')
    );
  }

  /**
   * Converts the storage id into its component parts.
   *
   * @param string $id
   *   The storage id. There are two formats that can potentially be used:
   *   - The first is the normal format that we actually store:
   *     "entity_type_id:bundle:view_mode:name"
   *   - The second is a special internal format we use in the IPE so we can
   *   correctly set context:
   *     "*entity_type_id:entity_id:view_mode:name"
   *
   * @return array
   *   An array with 4 or 5 items:
   *   - Entity type id: string
   *   - Bundle name: string
   *   - View mode: string
   *   - Default name: string
   *   - Entity: \Drupal\Core\Entity\EntityInterface|NULL
   */
  protected function parseId($id) {
    list ($entity_type_id, $part_two, $view_mode, $name) = explode(':', $id);

    if (strpos($entity_type_id, '*') === 0) {
      $entity_type_id = substr($entity_type_id, 1);
      $storage = $this->entityTypeManager->getStorage($entity_type_id);
      $entity = $storage->load($part_two);
      $bundle = $entity->bundle();
    }
    else {
      $entity = NULL;
      $bundle = $part_two;
    }

    return [$entity_type_id, $bundle, $view_mode, $name, $entity];
  }

  /**
   * {@inheritdoc}
   */
  public function load($id) {
    list ($entity_type_id, $bundle, $view_mode, $name, $entity) = $this->parseId($id);
    $panels_display = $this->panelizer->getDefaultPanelsDisplay($name, $entity_type_id, $bundle, $view_mode);
    // Set a placeholder context so that the calling code knows that we need
    // an entity context. If we have the value available, then we actually set
    // the context value.
    $contexts = [
      '@panelizer.entity_context:entity' => new Context(new ContextDefinition('entity:' . $entity_type_id, NULL, TRUE), $entity),
    ];
    $panels_display->setContexts($contexts);
    return $panels_display;
  }

  /**
   * {@inheritdoc}
   */
  public function save(PanelsDisplayVariant $panels_display) {
    $id = $panels_display->getStorageId();
    list ($entity_type_id, $bundle, $view_mode, $name) = $this->parseId($id);
    return $this->panelizer->setDefaultPanelsDisplay($name, $entity_type_id, $bundle, $view_mode, $panels_display);
  }

  /**
   * {@inheritdoc}
   */
  public function access($id, $op, AccountInterface $account) {
    list ($entity_type_id, $bundle, $view_mode, $name) = $this->parseId($id);
    if ($panels_display = $this->panelizer->getDefaultPanelsDisplay($name, $entity_type_id, $bundle, $view_mode)) {
      if ($op == 'change layout') {
        if ($this->panelizer->hasDefaultPermission('change layout', $entity_type_id, $bundle, $view_mode, $name, $account)) {
          return AccessResult::allowed();
        }
      }
      else if ($op == 'read' || $this->panelizer->hasDefaultPermission('change content', $entity_type_id, $bundle, $view_mode, $name, $account)) {
        return AccessResult::allowed();
      }
    }

    return AccessResult::forbidden();
  }

}