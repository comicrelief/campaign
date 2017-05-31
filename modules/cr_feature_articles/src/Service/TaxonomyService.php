<?php

namespace Drupal\cr_feature_articles\Service;

use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Entity\Query\QueryFactory;
/**
 * Class TaxonomyService
 *
 * @package Drupal\cr_feature_articles\Service
 */
class TaxonomyService
{

  /**
   * @var \Drupal\Core\Entity\Query\QueryFactory|\Drupal\Core\Entity\Query\QueryInterface
   */
  private $query;

  /**
   * @var \Drupal\Core\Entity\EntityManager
   */
  private $em;

  /**
   * TaxonomyService constructor.
   * @param \Drupal\Core\Entity\Query\QueryFactory $query
   * @param \Drupal\Core\Entity\EntityManager $em
   */
  public function __construct(QueryFactory $query, EntityManager $em) {
    $this->query = $query;
    $this->em = $em;
  }

  /**
   * @param int $tid
   * @param int $limit
   * @return \Drupal\Core\Entity\EntityInterface[]
   */
  public function getArticleNodesByTermId($tid, $limit = 4)
  {
    $nodeIds = $this->query->get('node', 'AND')
      ->condition('status', 1)
      ->condition('type', 'article')
      ->condition('field_article_category.entity.tid', $tid)
      ->sort('created', 'ASC')
      ->range(0, $limit)
      ->execute();

    return $this->em->getStorage('node')->loadMultiple($nodeIds);
  }

}
