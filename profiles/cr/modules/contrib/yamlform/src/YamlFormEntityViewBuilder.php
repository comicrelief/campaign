<?php

/**
 * @file
 * Contains \Drupal\yamlform\YamlFormEntityViewBuilder.
 */

namespace Drupal\yamlform;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;

/**
 * Render controller for YAML form.
 */
class YamlFormEntityViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {
    // TODO: Determine if form can be cached.
    /* @var $entity \Drupal\yamlform\YamlFormInterface */
    return [
      '#cache' => [
        'max-age' => 0,
      ],
    ] + $entity->getSubmissionForm();
  }

}
