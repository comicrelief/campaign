<?php

/**
 * @file
 * Contains \Drupal\ds\Plugin\DsField\Taxonomy\TaxonomyTermTitle.
 */

namespace Drupal\ds\Plugin\DsField\Taxonomy;

use Drupal\ds\Plugin\DsField\Title;

/**
 * Plugin that renders the title of a term.
 *
 * @DsField(
 *   id = "taxonomy_term_title",
 *   title = @Translation("Name"),
 *   entity_type = "taxonomy_term",
 *   provider = "taxonomy"
 * )
 */
class TaxonomyTermTitle extends Title {

  /**
   * {@inheritdoc}
   */
  public function entityRenderKey() {
    return 'name';
  }

}
