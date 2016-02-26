<?php

/**
 * @file
 * Contains Drupal\search_api_page\Entity\SearchApiPage.
 */

namespace Drupal\search_api_page\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\search_api\Entity\Index;
use Drupal\search_api_page\SearchApiPageInterface;

/**
 * Defines the Search page entity.
 *
 * @ConfigEntityType(
 *   id = "search_api_page",
 *   label = @Translation("Search page"),
 *   handlers = {
 *     "list_builder" = "Drupal\search_api_page\SearchApiPageListBuilder",
 *     "form" = {
 *       "add" = "Drupal\search_api_page\Form\SearchApiPageForm",
 *       "edit" = "Drupal\search_api_page\Form\SearchApiPageForm",
 *       "delete" = "Drupal\search_api_page\Form\SearchApiPageDeleteForm"
 *     }
 *   },
 *   config_prefix = "search_api_page",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/config/search/search-api-pages/{search_api_page}",
 *     "edit-form" = "/admin/config/search/search-api-pages/{search_api_page}/edit",
 *     "delete-form" = "/admin/config/search/search-api-pages/{search_api_page}/delete",
 *     "collection" = "/admin/config/search/search-api-pages"
 *   }
 * )
 */
class SearchApiPage extends ConfigEntityBase implements SearchApiPageInterface {

  /**
   * The Search page ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Search page label.
   *
   * @var string
   */
  protected $label;

  /**
   * The Search page path.
   *
   * @var string
   */
  protected $path;

  /**
   * Whether to use clean URLs or not.
   *
   * @var bool
   */
  protected $clean_url = TRUE;

  /**
   * Whether to show all resluts when no search is performed.
   *
   * @var bool
   */
  protected $show_all_when_no_keys = FALSE;

  /**
   * The Search Api index.
   *
   * @var string
   */
  protected $index;

  /**
   * The limit per page.
   *
   * @var string
   */
  protected $limit = 10;

  /**
   * The searched fields.
   *
   * @var array
   */
  protected $searched_fields = array();

  /**
   * The style of the results.
   *
   * @var string
   */
  protected $style = 'view_modes';

  /**
   * The view mode configuration.
   *
   * @var array
   */
  protected $view_mode_configuration = array();

  /**
   * Whether to show the search form above search results.
   *
   * @var bool
   */
  protected $show_search_form = TRUE;

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();
    $this->addDependency('config', Index::load($this->getIndex())->getConfigDependencyName());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPath() {
    return $this->path;
  }

  /**
   * {@inheritdoc}
   */
  public function getCleanUrl() {
    return $this->clean_url;
  }

  /**
   * {@inheritdoc}
   */
  public function getIndex() {
    return $this->index;
  }

  /**
   * {@inheritdoc}
   */
  public function getLimit() {
    return $this->limit;
  }

  /**
   * {@inheritdoc}
   */
  public function getSearchedFields() {
    return $this->searched_fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getFulltextFields() {
    $fields = array();
    if (!empty($this->index)) {
      /* @var  $index \Drupal\search_api\IndexInterface */
      $index = Index::load($this->index);

      $fields_info = $index->getFields();
      foreach ($index->getFulltextFields() as $field_id) {
        $fields[$field_id] = $fields_info[$field_id]->getPrefixedLabel();
      }
    }

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getStyle() {
    return $this->style;
  }

  /**
   * {@inheritdoc}
   */
  public function getViewModeConfiguration() {
    return $this->view_mode_configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function renderAsViewModes() {
    return $this->getStyle() == 'view_modes';
  }

  /**
   * {@inheritdoc}
   */
  public function renderAsSnippets() {
    return $this->getStyle() == 'search_results';
  }

  /**
   * {@inheritdoc}
   */
  public function showSearchForm() {
    return $this->show_search_form;
  }

  /**
   * {@inheritdoc}
   */
  public function showAllResultsWhenNoSearchIsPerformed() {
    return $this->show_all_when_no_keys;
  }

}
