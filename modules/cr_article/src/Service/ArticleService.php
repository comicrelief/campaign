<?php

namespace Drupal\cr_article\Service;

use Drupal\Core\StringTranslation\TranslationManager;

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
    $taxonomy_nids = array();
    $results = array('All' => $this->translationManager->translate('All'));

    // Get all of the article ids for the current page type.
    $articleIds = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('field_article_type', $type)
      ->execute();

    // Get all of the current taxonomy nids for the page type.
    foreach (\Drupal\node\Entity\Node::loadMultiple($articleIds) as $entity) {
      if (isset($entity->get('field_article_category')[0])) {
        $taxonomy_nid = $res = $entity->get('field_article_category')[0]->getValue()['target_id'];
        $taxonomy_nids[$taxonomy_nid] = $taxonomy_nid;
      }
    }

    // Loop through the available terms and add to the options array.
    foreach (\Drupal\taxonomy\Entity\Term::loadMultiple($taxonomy_nids) as $taxonomy) {
      $results[$taxonomy->id()] = $taxonomy->getName();
    }

    // Sort the array by value.
    asort($results);

    return $results;
  }

}
