<?php

namespace Drupal\diff;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;

interface DiffLayoutInterface extends PluginFormInterface, ConfigurablePluginInterface {

  /**
   * Builds a diff comparison between two revisions.
   *
   * This method is responsible for building the diff comparison between
   * revisions of the same entity. It can build a table, navigation links and
   * headers of a diff comparison.
   *
   * @see \Drupal\Plugin\Layout\ClassicDiffLayout
   *
   * @param \Drupal\Core\Entity\EntityInterface $left_revision
   *   The left revision.
   * @param \Drupal\Core\Entity\EntityInterface $right_revision
   *   The right revision.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return mixed
   *   The modified build array that the plugin builds.
   */
  public function build(EntityInterface $left_revision, EntityInterface $right_revision, EntityInterface $entity);
}
