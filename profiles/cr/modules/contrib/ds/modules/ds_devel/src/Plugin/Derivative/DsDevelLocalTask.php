<?php

namespace Drupal\ds_devel\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides local task definitions for all entity bundles.
 */
class DsDevelLocalTask extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates an DevelLocalTask object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The translation manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, TranslationInterface $string_translation) {
    $this->entityTypeManager = $entity_type_manager;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('string_translation')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = array();

    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      $has_markup_path = $entity_type->hasLinkTemplate('devel-markup');
      if ($has_markup_path) {
        $this->derivatives["$entity_type_id.devel_markup_tab"] = array(
          'route_name' => "entity.$entity_type_id.devel_markup",
          'weight' => 100,
          'title' => $this->t('Markup'),
          'parent_id' => "devel.entities:$entity_type_id.devel_tab",
        );
      }
    }

    foreach ($this->derivatives as &$entry) {
      $entry += $base_plugin_definition;
    }

    return $this->derivatives;
  }

}
