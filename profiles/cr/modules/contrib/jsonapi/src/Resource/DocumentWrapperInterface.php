<?php


namespace Drupal\jsonapi\Resource;

/**
 * Class DocumentWrapperInterface.
 *
 * @package Drupal\jsonapi\Resource
 */
interface DocumentWrapperInterface {

  /**
   * Gets the data.
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\jsonapi\EntityCollectionInterface
   *   The data.
   */
  public function getData();

}
