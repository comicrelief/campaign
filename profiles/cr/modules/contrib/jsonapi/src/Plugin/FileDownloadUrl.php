<?php

namespace Drupal\jsonapi\Plugin;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Session\AccountInterface;

class FileDownloadUrl extends FieldItemList {

  /**
   * Creates a relative URL out of a URI.
   *
   * This is a wrapper to the procedural code for testing purposes. For obvious
   * reasons this method will not be unit tested, but that is fine since it's
   * only using already tested Drupal API functions.
   *
   * @param string $uri
   *   The URI to transform.
   *
   * @return string
   *   The transformed relative URL.
   */
  protected function fileCreateRootRelativeUrl($uri) {
    return file_url_transform_relative(file_create_url($uri));
  }

  /**
   * {@inheritdoc}
   */
  public function getValue($include_computed = FALSE) {
    $this->initList();

    return parent::getValue($include_computed);
  }

  /**
   * {@inheritdoc}
   */
  public function access($operation = 'view', AccountInterface $account = NULL, $return_as_object = FALSE) {
    return $this->getEntity()
      ->get('uri')
      ->access($operation, $account, $return_as_object);
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return $this->getEntity()->get('uri')->isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function getIterator() {
    $this->initList();

    return parent::getIterator();
  }

  /**
   * {@inheritdoc}
   */
  public function get($index) {
    $this->initList();

    return parent::get($index);
  }

  /**
   * Initialize the internal field list with the modified items.
   */
  protected function initList() {
    if ($this->list) {
      return;
    }
    $url_list = [];
    foreach ($this->getEntity()->get('uri') as $uri_item) {
      $url_item = clone $uri_item;
      $uri = $uri_item->value;
      $url_item->setValue($this->fileCreateRootRelativeUrl($uri));
      $url_list[] = $url_item;
    }
    $this->list = $url_list;
  }

}
