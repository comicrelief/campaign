<?php

/**
 * @file
 * Contains Drupal\paragraphs\Controller\ParagraphsTypeListBuilder.
 */

namespace Drupal\paragraphs\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of ParagraphsType.
 */
class ParagraphsTypeListBuilder extends ConfigEntityListBuilder
{

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Paragraphs types');
    $header['id'] = $this->t('Machine name');
    return $header + parent::buildHeader();
  }

  /**
  * {@inheritdoc}
  */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $this->getLabel($entity);
    $row['id'] = $entity->id();
    // You probably want a few more properties here...
    return $row + parent::buildRow($entity);
  }
}
