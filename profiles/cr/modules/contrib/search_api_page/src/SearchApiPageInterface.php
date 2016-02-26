<?php

/**
 * @file
 * Contains Drupal\search_api_page\SearchApiPageInterface.
 */

namespace Drupal\search_api_page;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Search page entities.
 */
interface SearchApiPageInterface extends ConfigEntityInterface {

  /**
   * Return the path.
   *
   * @return string
   *   The path.
   */
  public function getPath();

  /**
   * Return the clean URL configuration.
   *
   * @return bool
   *   The clean url.
   */
  public function getCleanUrl();

  /**
   * Return the search api index.
   *
   * @return string
   *   The index.
   */
  public function getIndex();

  /**
   * Return the limit per page.
   *
   * @return int
   *   The page limit.
   */
  public function getLimit();

  /**
   * Return the searched fields.
   *
   * @return string[]
   *   A collection of searched fields.
   */
  public function getSearchedFields();

  /**
   * Retrieves a list of all available fulltext fields.
   *
   * @return string[]
   *   An options list of fulltext field identifiers mapped to their prefixed
   *   labels.
   */
  public function getFullTextFields();

  /**
   * Get the style to render the search results in.
   *
   * @return string
   *   The style.
   */
  public function getStyle();

  /**
   * Get the view mode configuration per entity for rendering.
   *
   * @return string[]
   *   A collection of view mode configuration.
   */
  public function getViewModeConfiguration();

  /**
   * Whether to render the results as view modes.
   *
   * @return bool
   *   TRUE when rendering as view modes.
   */
  public function renderAsViewModes();

  /**
   * Whether to render the results as snippets.
   *
   * @return bool
   *   TRUE when rendering as snippets.
   */
  public function renderAsSnippets();

  /**
   * Whether to show the search form above the search results.
   *
   * @return bool
   *   TRUE when search form needs to be shown.
   */
  public function showSearchForm();

  /**
   * Show all results when no search is performed.
   *
   * @return bool
   *   TRUE when having to show all results.
   */
  public function showAllResultsWhenNoSearchIsPerformed();

}
