<?php

namespace Drupal\search_api_solr;

use Drupal\search_api\Backend\BackendInterface;
use Drupal\search_api\IndexInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api_solr\Solr\SolrHelper;

/**
 * Defines an interface for Solr search backend plugins.
 *
 * It extends the generic \Drupal\search_api\Backend\BackendInterface and covers
 * additional Solr specific methods.
 */
interface SolrBackendInterface extends BackendInterface {

  /**
   * Returns the solr helper class.
   *
   * @return \Drupal\search_api_solr\Solr\SolrHelper
   *   The Solr helper class.
   */
  public function getSolrHelper();

  /**
   * Sets the Solr helper class.
   *
   * @param \Drupal\search_api_solr\Solr\SolrHelper $solrHelper
   *   The Solr helper class.
   */
  public function setSolrHelper(SolrHelper $solrHelper);

  /**
   * Returns the Solarium client.
   *
   * @return \Solarium\Client
   *   The solarium instance object.
   */
  public function getSolr();

  /**
   * Creates a list of all indexed field names mapped to their Solr field names.
   *
   * The special fields "search_api_id" and "search_api_relevance" are also
   * included. Any Solr fields that exist on search results are mapped back to
   * to their local field names in the final result set.
   *
   * @param \Drupal\search_api\IndexInterface $index
   *   The Search Api index.
   * @param bool $reset
   *   (optional) Whether to reset the static cache.
   *
   * @see SearchApiSolrBackend::search()
   */
  public function getSolrFieldNames(IndexInterface $index, $reset = FALSE);

  /**
   * Gets the currently used Solr connection object.
   *
   * @return \Solarium\Client
   *   The solr connection object used by this server.
   */
  public function getSolrConnection();

  /**
   * Retrieves a config file or file list from the Solr server.
   *
   * Uses the admin/file request handler.
   *
   * @param string|null $file
   *   (optional) The name of the file to retrieve. If the file is a directory,
   *   the directory contents are instead listed and returned. NULL represents
   *   the root config directory.
   *
   * @return \Solarium\Core\Client\Response
   *   A Solarium response object containing either the file contents or a file
   *   list.
   *
   * @throws \Drupal\search_api_solr\SearchApiSolrException
   */
  public function getFile($file = NULL);

  /**
   * Retrieves a Solr document from an search api index item.
   *
   * @param \Drupal\search_api\IndexInterface $index
   *   The search api index.
   * @param \Drupal\search_api\Item\ItemInterface $item
   *   An item to get documents for.
   *
   * @return \Solarium\QueryType\Update\Query\Document\Document
   *   A solr document.
   */
  public function getDocument(IndexInterface $index, ItemInterface $item);

  /**
   * Retrieves Solr documents from search api index items.
   *
   * @param \Drupal\search_api\IndexInterface $index
   *   The search api index.
   * @param \Drupal\search_api\Item\ItemInterface[] $items
   *   An array of items to get documents for.
   *
   * @return \Solarium\QueryType\Update\Query\Document\Document[]
   *   An array of solr documents.
   */
  public function getDocuments(IndexInterface $index, array $items);

}
