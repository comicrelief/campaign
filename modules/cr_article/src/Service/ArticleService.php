<?php

namespace Drupal\cr_article\Service;

use Drupal\Core\StringTranslation\TranslationManager;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

/**
 * Class ArticleService
 *
 * @package Drupal\cr_article\Service
 */
class ArticleService {

  const TYPE_NEWS = 'news';
  const TYPE_PRESS_RELEASE = 'press-releases';

  /**
   * @var TranslationManager
   */
  protected $translationManager;

  /**
   * ColourService constructor.
   *
   * @param TranslationManager $translationManager
   */
  public function __construct(TranslationManager $translationManager) {
    $this->translationManager = $translationManager;
  }

  /**
   * Get available article type.
   * @return array
   */
  public function getArticleTypes()
  {
    return array(
      $this::TYPE_NEWS          => $this->translationManager->translate('News'),
      $this::TYPE_PRESS_RELEASE => $this->translationManager->translate('Press Releases')
    );
  }

  /**
   * Get an array of available taxonomy options for an article type.
   * @param $type
   * @return array
   */
  public function getArticleTypeAvailableTaxonomies($type)
  {
    // Define the results array.
    $results = array('All' => $this->translationManager->translate('All'));

    // Define query to get taxonomies on articles by type.
    $query = \Drupal::database()->select('taxonomy_term_field_data', 'term');
    $query->fields('term', ['tid', 'name']);
    $query->join('node__field_article_category', 'category', 'term.tid = category.field_article_category_target_id');
    $query->join('node', 'node', 'node.nid = category.entity_id');
    $query->join('node__field_article_type', 'article_type', 'node.nid = article_type.entity_id');
    $query->join('node_field_data', 'field_data', 'node.nid = field_data.nid');
    $query->condition('node.type', 'article');
    $query->condition('article_type.field_article_type_value', $type);
    $query->condition('field_data.status', '1');
    $query->groupBy('term.tid, term.name');

    foreach ($query->execute()->fetchAllAssoc('tid') as $item) {
      $results[$item->tid] = $this->translationManager->translate($item->name);
    }

    // Sort the array by value.
    asort($results);

    return $results;
  }
}
