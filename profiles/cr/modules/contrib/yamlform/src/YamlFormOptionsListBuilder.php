<?php

namespace Drupal\yamlform;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\OptGroup;

/**
 * Defines a class to build a listing of form options entities.
 *
 * @see \Drupal\yamlform\Entity\YamlFormOption
 */
class YamlFormOptionsListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Label');
    $header['id'] = $this->t('ID');
    $header['options'] = [
      'data' => $this->t('Options'),
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\yamlform\YamlFormOptionsInterface $entity */
    $row['label'] = $entity->toLink($entity->label(), 'edit-form');
    $row['id'] = $entity->id();
    $options = OptGroup::flattenOptions($entity->getOptions());
    foreach ($options as $key => &$value) {
      if ($key != $value) {
        $value .= ' (' . $key . ')';
      }
    }
    $row['options'] = implode('; ', array_slice($options, 0, 12)) . (count($options) > 12 ? '; ...' : '');
    return $row + parent::buildRow($entity);
  }

}
