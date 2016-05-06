<?php
/**
 * @file
 * Contains Drupal\yamlform\YamlFormOptionsListBuilder.
 */

namespace Drupal\yamlform;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines a class to build a listing of YAML form options entities.
 *
 * @see \Drupal\yamlform\Entity\YamlFormOption
 */
class YamlFormOptionsListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Label');
    $header['id'] = $this->t('id');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->toLink();
    $row['id'] = $entity->id();
    return $row + parent::buildRow($entity);
  }

}
