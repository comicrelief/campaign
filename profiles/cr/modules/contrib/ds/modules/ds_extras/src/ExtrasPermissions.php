<?php

/**
 * @file
 * Contains \Drupal\field_ui\FieldUiPermissions.
 */

namespace Drupal\ds_extras;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ds\Ds;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dynamic permissions of the ds extras module.
 */
class extrasPermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new FieldUiPermissions instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'));
  }

  /**
   * Returns an array of ds extras permissions.
   *
   * @return array
   */
  public function extrasPermissions() {
    $permissions = [];

    if (\Drupal::config('ds_extras.settings')->get('field_permissions')) {
      $entities = $this->entityTypeManager->getDefinitions();
      foreach ($entities as $entity_type => $info) {
        // @todo do this on all fields ?
        // @todo hide switch field if enabled
        $fields = Ds::getFields($entity_type);
        foreach ($fields as $key => $finfo) {
          $permissions['view ' . $key . ' on ' . $entity_type] = array(
            'title' => t('View @field on @entity_type', array('@field' => $finfo['title'], '@entity_type' => $info->getLabel())),
          );
        }
      }
    }

    return $permissions;
  }

}
