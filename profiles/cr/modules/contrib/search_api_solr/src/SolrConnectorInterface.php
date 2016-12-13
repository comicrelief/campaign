<?php

namespace Drupal\search_api_solr;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Solarium\Core\Client\Endpoint;
use Solarium\Core\Client\Response;
use Solarium\Core\Query\QueryInterface;
use Solarium\Core\Query\Result\ResultInterface;

interface SolrConnectorInterface extends ConfigurablePluginInterface {

  /**
   * Returns a link to the Solr server.
   *
   * @return \Drupal\Core\Link
   */
  public function getServerLink();

  /**
   * Returns a link to the Solr core, if the necessary options are set.
   *
   * @return \Drupal\Core\Link
   */
  public function getCoreLink();

  /**
   * Gets the current Solr version.
   *
   * @param bool $force_auto_detect
   *   If TRUE, ignore user overwrites.
   *
   * @return string
   *   The full Solr version string.
   */
  public function getSolrVersion($force_auto_detect = FALSE);

  /**
   * Gets the current Solr major version.
   *
   * @param string $version
   *   An optional Solr version string.
   *
   * @return int
   *   The Solr major version.
   */
  public function getSolrMajorVersion($version = '');

  /**
   * Gets the current Solr branch name.
   *
   * @param string $version
   *   An optional Solr version string.
   *
   * @return string
   *   The Solr branch string.
   */
  public function getSolrBranch($version = '');

  /**
   * Gets the LuceneMatchVersion string.
   *
   * @param string $version
   *   An optional Solr version string.
   *
   * @return string
   *   The lucene match version in V.V format.
   */
  public function getLuceneMatchVersion($version = '');

  /**
   * Gets information about the Solr server.
   *
   * @param boolean $reset
   *   If TRUE the server will be asked regardless if a previous call is cached.
   *
   * @return object
   *   A response object with server information.
   *
   * @throws \Drupal\search_api_solr\SearchApiSolrException
   */
  public function getServerInfo($reset = FALSE);

  /**
   * Gets information about the Solr Core.
   *
   * @param boolean $reset
   *   If TRUE the server will be asked regardless if a previous call is cached.
   *
   * @return object
   *   A response object with system information.
   *
   * @throws \Drupal\search_api_solr\SearchApiSolrException
   */
  public function getCoreInfo($reset = FALSE);

  /**
   * Gets meta-data about the index.
   *
   * @return object
   *   A response object filled with data from Solr's Luke.
   *
   * @throws \Drupal\search_api_solr\SearchApiSolrException
   */
  public function getLuke();

  /**
   * Gets the full schema version string the core is using.
   *
   * @param boolean $reset
   *   If TRUE the server will be asked regardless if a previous call is cached.
   *
   * @return string
   *   The full schema version string.
   */
  public function getSchemaVersionString($reset = FALSE);

  /**
   * Gets the schema version number.
   *
   * @param boolean $reset
   *   If TRUE the server will be asked regardless if a previous call is cached.
   *
   * @return string
   *   The full schema version string.
   */
  public function getSchemaVersion($reset = FALSE);

  /**
   * Pings the Solr core to tell whether it can be accessed.
   *
   * @return mixed
   *   The latency in milliseconds if the core can be accessed,
   *   otherwise FALSE.
   */
  public function pingCore();

  /**
   * Pings the Solr server to tell whether it can be accessed.
   *
   * @return mixed
   *   The latency in milliseconds if the core can be accessed,
   *   otherwise FALSE.
   */
  public function pingServer();

  /**
   * Gets summary information about the Solr Core.
   *
   * @return array
   *   An array of stats about the solr core.
   *
   * @throws \Drupal\search_api_solr\SearchApiSolrException
   */
  public function getStatsSummary();

  /**
   * Sends a REST GET request to the Solr core and returns the result.
   *
   * @param string $path
   *   The path to append to the base URI.
   *
   * @return string
   *   The decoded response.
   */
  public function coreRestGet($path);

  /**
   * Sends a REST POST request to the Solr core and returns the result.
   *
   * @param string $path
   *   The path to append to the base URI.
   * @param string $command_json
   *   The command to send encoded as JSON.
   *
   * @return string
   *   The decoded response.
   */
  public function coreRestPost($path, $command_json = '');

  /**
   * Sends a REST GET request to the Solr server and returns the result.
   *
   * @param string $path
   *   The path to append to the base URI.
   *
   * @return string
   *   The decoded response.
   */
  public function serverRestGet($path);

  /**
   * Sends a REST POST request to the Solr server and returns the result.
   *
   * @param string $path
   *   The path to append to the base URI.
   * @param string $command_json
   *   The command to send encoded as JSON.
   *
   * @return string
   *   The decoded response.
   */
  public function serverRestPost($path, $command_json = '');

  /**
   * Gets the current Solarium update query, creating one if necessary.
   *
   * @return \Solarium\QueryType\Update\Query\Query
   *   The Update query.
   */
  public function getUpdateQuery();

  /**
   * Gets the current Solarium update query, creating one if necessary.
   *
   * @return \Solarium\QueryType\Select\Query\Query
   *   The Select query.
   */
  public function getSelectQuery();

  /**
   * Returns a Solarium query helper object.
   *
   * @param \Solarium\Core\Query\QueryInterface|null $query
   *   (optional) A Solarium query object.
   *
   * @return \Solarium\Core\Query\Helper
   *   A Solarium query helper.
   */
  public function getQueryHelper(QueryInterface $query = NULL);

  /**
   * Executes a search query and returns the raw response.
   *
   * @param \Solarium\QueryType\Select\Query\Query $query
   * @param \Solarium\Core\Client\Endpoint|NULL $endpoint
   *
   * @return Response
   */
  public function search(\Solarium\QueryType\Select\Query\Query $query, Endpoint $endpoint = NULL);

  /**
   * Creates a result from a response.
   *
   * @param \Solarium\QueryType\Select\Query\Query $query
   * @param \Solarium\Core\Client\Response $response
   *
   * @return ResultInterface
   */
  public function createSearchResult(\Solarium\QueryType\Select\Query\Query $query, Response $response);

  /**
   * Executes an update query and applies some tweaks.
   *
   * @param \Solarium\QueryType\Update\Query\Query $query
   * @param \Solarium\Core\Client\Endpoint|NULL $endpoint
   *
   * @return ResultInterface
   */
  public function update(\Solarium\QueryType\Update\Query\Query $query, Endpoint $endpoint = NULL);

  /**
   * Executes any query.
   *
   * @param \Solarium\Core\Query\QueryInterface $query
   * @param \Solarium\Core\Client\Endpoint|NULL $endpoint
   *
   * @return ResultInterface
   */
  public function execute(QueryInterface $query, Endpoint $endpoint = NULL);

  /**
   * Optimizes the Solr index.
   *
   * @param \Solarium\Core\Client\Endpoint|NULL $endpoint
   */
  public function optimize(Endpoint $endpoint = NULL);

  /**
   * Returns an endpoint.
   *
   * @param string $key
   *
   * @return \Solarium\Core\Client\Endpoint
   */
  public function getEndpoint($key = 'core');

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
   * Returns additional, connector-specific information about this server.
   *
   * This information will be then added to the server's "View" tab in some way.
   * In the default theme implementation the data is output in a table with two
   * columns along with other, generic information about the server.
   *
   * @return array
   *   An array of additional server information, with each piece of information
   *   being an associative array with the following keys:
   *   - label: The human-readable label for this data.
   *   - info: The information, as HTML.
   *   - status: (optional) The status associated with this information. One of
   *     "info", "ok", "warning" or "error". Defaults to "info".
   */
  public function viewSettings();

}
