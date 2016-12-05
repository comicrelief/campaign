<?php

namespace Drupal\jsonapi\Resource;


/**
 * Class DocumentWrapper.
 *
 * @package Drupal\jsonapi\Resource
 */
class DocumentWrapper implements DocumentWrapperInterface {

  /**
   * The data to normalize.
   *
   * @var \Drupal\Core\Entity\EntityInterface|\Drupal\jsonapi\EntityCollectionInterface
   */
  protected $data;

  /**
   * Instantiates a DocumentRootNormalizerValue object.
   *
   * @param \Drupal\Core\Entity\EntityInterface|\Drupal\jsonapi\EntityCollectionInterface $data
   *   The data to normalize. It can be either a straight up entity or a
   *   collection of entities.
   */
  public function __construct($data) {
    $this->data = $data;
  }

  /**
   * {@inheritdoc}
   */
  public function getData() {
    return $this->data;
  }

}
