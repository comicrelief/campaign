<?php

/**
 * @file
 * Contains Drupal\search_api_page\SearchApiPageListBuilder.
 */

namespace Drupal\search_api_page;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides a listing of Search page entities.
 */
class SearchApiPageListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Title');
    $header['path'] = $this->t('Path');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\search_api_page\SearchApiPageInterface */
    $row['label'] = $entity->label();
    $path = $entity->getPath();
    if (!empty($path)) {
      $row['path'] = Link::fromTextAndUrl($entity->getPath(), Url::fromRoute('search_api_page.' . \Drupal::languageManager()->getDefaultLanguage()->getId() . '.' . $entity->id()));
    }
    else {
      $row['path'] = '';
    }
    return $row + parent::buildRow($entity);
  }

}
