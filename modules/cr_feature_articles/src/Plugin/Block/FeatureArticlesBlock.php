<?php

namespace Drupal\cr_feature_articles\Plugin\Block;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'FeatureArticles' Block.
 *
 * @Block(
 *   id = "cr_feature_articles",
 *   admin_label = @Translation("Feature Articles"),
 * )
 */
class FeatureArticlesBlock extends BlockBase implements ContainerFactoryPluginInterface, BlockPluginInterface {

  /**
   * @var EntityManager
   */
  private $entityManager;

  /**
   * @var QueryInterface
   */
  private $entityQuery;

  /**
   * FeatureArticlesBlock constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param ContainerInterface $container
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ContainerInterface $container) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityQuery = $container->get('entity.query');
    $this->entityManager = $container->get('entity.manager');
  }

  /**
   * @param ContainerInterface $container
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    // Get the block configuration.
    $config = $this->getConfiguration();

    // Find nodes with matching taxonomy id's and sort by date created.
    $nodeIds = $this->entityQuery->get('node', 'AND')
      ->condition('status', 1)
      ->condition('type', 'article')
      ->condition('field_article_category.entity.tid', $config['feature_articles_block_category'])
      ->sort('created', 'ASC')
      ->range(0, 4)
      ->execute();

    return [
      '#theme' => 'cr_feature_articles',
      '#markup' => 'This is the feature articles block, Use block--cr-feature-articles.html.twig in your theme templates folder to override it',
      'articles' => $this->entityManager->getStorage('node')
        ->loadMultiple($nodeIds),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    // Get the block configuration.
    $config = $this->getConfiguration();

    // Get the article category tid's.
    $tids = $this->entityQuery->get('taxonomy_term', 'AND')
      ->condition('vid', "article_category")
      ->execute();

    // Add the article tid's into an array.
    $options = [];
    foreach ($this->entityManager->getStorage('taxonomy_term')
               ->loadMultiple($tids) as $tid => $term) {
      /** @var $term Term */
      $options[$tid] = $term->getName();
    }

    $form['feature_articles_block_category'] = [
      '#type' => 'select',
      '#title' => t('Choose article category'),
      '#multiple' => FALSE,
      '#options' => $options,
      '#description' => t('Click on an article category to select'),
      '#required' => TRUE,
      '#default_value' => isset($config['feature_articles_block_category']) ? $config['feature_articles_block_category'] : '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['feature_articles_block_category'] = $values['feature_articles_block_category'];
  }

}
