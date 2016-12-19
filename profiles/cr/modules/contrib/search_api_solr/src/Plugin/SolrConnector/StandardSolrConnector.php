<?php

namespace Drupal\search_api_solr\Plugin\SolrConnector;

use Drupal\Core\Annotation\Translation;
use Drupal\search_api_solr\Annotation\SolrConnector;
use Drupal\search_api_solr\SolrConnector\SolrConnectorPluginBase;

/**
 * Standard Solr connector.
 *
 * @SolrConnector(
 *   id = "standard",
 *   label = @Translation("Standard"),
 *   description = @Translation("A standard connector usable for local installations of the standard Solr distribution.")
 * )
 */
class StandardSolrConnector extends SolrConnectorPluginBase {

}
