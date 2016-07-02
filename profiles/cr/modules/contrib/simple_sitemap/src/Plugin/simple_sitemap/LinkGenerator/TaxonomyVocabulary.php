<?php
/**
 * @file
 * Contains \Drupal\simple_sitemap\Plugin\simple_sitemap\LinkGenerator\TaxonomyVocabulary.
 *
 * Plugin for taxonomy term entity link generation.
 */

namespace Drupal\simple_sitemap\Plugin\simple_sitemap\LinkGenerator;

use Drupal\simple_sitemap\LinkGeneratorBase;

/**
 * TaxonomyVocabulary class.
 *
 * @LinkGenerator(
 *   id = "taxonomy_vocabulary",
 *   entity_type_name = "taxonomy_term"
 * )
 */
class TaxonomyVocabulary extends LinkGeneratorBase {

  /**
   * {@inheritdoc}
   */
  public function getQueryInfo() {
    return array(
      'field_info' => array(
        'entity_id' => 'tid',
        'lastmod' => 'changed',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery($bundle) {
    return $this->database->select('taxonomy_term_field_data', 't')
      ->fields('t', array('tid', 'changed'))
      ->condition('vid', $bundle);
  }

}
