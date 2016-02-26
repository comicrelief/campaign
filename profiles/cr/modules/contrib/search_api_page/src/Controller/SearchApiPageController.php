<?php

/**
 * @file
 * Contains \Drupal\search_api_page\Controller\SearchApiPageController.
 */

namespace Drupal\search_api_page\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\search_api\Entity\Index;
use Drupal\search_api_page\Entity\SearchApiPage;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a controller to serve search pages.
 */
class SearchApiPageController extends ControllerBase {

  /**
   * Router callback.
   *
   * @param Request $request
   *   The request.
   * @param string $search_api_page_name
   *   The search api page name.
   * @param string $keys
   *   The search word.
   *
   * @return array $build
   *   The page build.
   */
  public function page(Request $request, $search_api_page_name, $keys = '') {
    $build = array();

    /* @var $search_api_page \Drupal\search_api_page\SearchApiPageInterface */
    $search_api_page = SearchApiPage::load($search_api_page_name);

    // Keys can be in the query.
    if (!$search_api_page->getCleanUrl()) {
      $keys = $request->get('keys');
    }

    // Page title.
    $build['#title'] = $search_api_page->label();

    // Show the search form.
    if ($search_api_page->showSearchForm()) {
      $args = array(
        'search_api_page' => $search_api_page->id(),
        'keys' => $keys,
      );
      $build['#form'] = $this->formBuilder()->getForm('Drupal\search_api_page\Form\SearchApiPageBlockForm', $args);
    }

    $perform_search = TRUE;
    if (empty($keys) && !$search_api_page->showAllResultsWhenNoSearchIsPerformed()) {
      $perform_search = FALSE;
    }

    if ($perform_search) {

      /* @var $search_api_index \Drupal\search_api\IndexInterface */
      $search_api_index = Index::load($search_api_page->getIndex());

      // Create the query.
      $query = $search_api_index->query([
        'parse_mode' => 'direct',
        'limit' => $search_api_page->getLimit(),
        'offset' => !is_null($request->get('page')) ? $request->get('page') : 0,
        'search id' => 'search_api_page:' . $search_api_page->id(),
      ]);

      // Search for keys.
      if (!empty($keys)) {
        $query->keys($keys);
      }

      // Index fields.
      $query->setFulltextFields($search_api_page->getSearchedFields());

      $result = $query->execute();
      $items = $result->getResultItems();

      /* @var $item \Drupal\search_api\Item\ItemInterface*/
      $results = array();
      foreach ($items as $item) {

        list(, $path, $langcode) = explode(':', $item->getId());
        list($entity_type, $id) = explode('/', $path);

        // Get the entity.
        $entity = $this->entityTypeManager()->getStorage($entity_type)->load($id);

        // Render as view modes.
        if ($search_api_page->renderAsViewModes()) {
          $key = 'entity:' . $entity_type . '_' . $entity->bundle();
          $view_mode_configuration = $search_api_page->getViewModeConfiguration();
          $view_mode = isset($view_mode_configuration[$key]) ? $view_mode_configuration[$key] : 'default';
          $results[] = $this->entityTypeManager()->getViewBuilder($entity_type)->view($entity, $view_mode);
        }

        // Render as snippets.
        if ($search_api_page->renderAsSnippets()) {
          $results[] = array(
            '#theme' => 'search_api_page_result',
            '#item' => $item,
            '#entity' => $entity,
          );
        }
      }

      if (!empty($results)) {

        $build['#search_title'] = array(
          '#markup' => $this->t('Search results'),
        );

        $build['#no_of_results'] = array(
          '#markup' => $this->formatPlural($result->getResultCount(), '1 result found', '@count results found'),
        );

        $build['#results'] = $results;

        // Build pager.
        pager_default_initialize($result->getResultCount(), $search_api_page->getLimit());
        $build['#pager'] = array(
          '#type' => 'pager',
        );
      }
      elseif ($perform_search) {
        $build['#no_results_found'] = array(
          '#markup' => $this->t('Your search yielded no results.'),
        );

        $build['#search_help'] = array(
          '#markup' => $this->t('<ul>
<li>Check if your spelling is correct.</li>
<li>Remove quotes around phrases to search for each word individually. <em>bike shed</em> will often show more results than <em>&quot;bike shed&quot;</em>.</li>
<li>Consider loosening your query with <em>OR</em>. <em>bike OR shed</em> will often show more results than <em>bike shed</em>.</li>
</ul>'),
        );
      }
    }

    $build['#theme'] = 'search_api_page';

    // TODO caching dependencies.
    return $build;
  }

}
