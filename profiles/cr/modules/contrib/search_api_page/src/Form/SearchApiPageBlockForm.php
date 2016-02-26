<?php

/**
 * @file
 * Contains \Drupal\search_api_page\Form\SearchApiPageBlockForm.
 */

namespace Drupal\search_api_page\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\search_api_page\Entity\SearchApiPage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds the search form for the search api page block.
 */
class SearchApiPageBlockForm extends FormBase {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a new SearchBlockForm.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(LanguageManagerInterface $language_manager, RendererInterface $renderer) {
    $this->languageManager = $language_manager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('language_manager'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'search_api_page_block_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $args = array()) {
    /* @var $search_api_page \Drupal\search_api_page\SearchApiPageInterface */
    $search_api_page = SearchApiPage::load($args['search_api_page']);
    $route = 'search_api_page.' . $this->languageManager->getCurrentLanguage()->getId() . '.' . $search_api_page->id();
    $form['#action'] = $this->getUrlGenerator()->generateFromRoute($route);

    $form['search_api_page'] = array(
      '#type' => 'value',
      '#value' => $search_api_page->id(),
    );

    $form['keys'] = array(
      '#type' => 'search',
      '#title' => $this->t('Search'),
      '#title_display' => 'invisible',
      '#size' => 15,
      '#default_value' => isset($args['keys']) ? $args['keys'] : '',
      '#attributes' => array('title' => $this->t('Enter the terms you wish to search for.')),
    );

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Search'),
    );

    if (!$search_api_page->getCleanUrl()) {
      $form['#method'] = 'get';
      $form['actions']['submit']['#name'] = '';
    }

    // Dependency on search api config entity.
    $this->renderer->addCacheableDependency($form, $search_api_page->getConfigDependencyName());

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // This form submits to the search page, so processing happens there.
    $keys = $form_state->getValue('keys');
    /* @var $searchApiPage \Drupal\search_api_page\SearchApiPageInterface */
    $form_state->setRedirectUrl(Url::fromRoute('search_api_page.' . $this->languageManager->getCurrentLanguage()->getId() . '.' . $form_state->getValue('search_api_page'), array('keys' => $keys)));
  }

}
