<?php
/**
 * @file
 * Contains \Drupal\simple_sitemap\SitemapGenerator.
 *
 * Generates a sitemap for entities and custom links.
 */

namespace Drupal\simple_sitemap;

use \XMLWriter;

/**
 * SitemapGenerator class.
 */
class SitemapGenerator {

  const XML_VERSION = '1.0';
  const ENCODING = 'UTF-8';
  const XMLNS = 'http://www.sitemaps.org/schemas/sitemap/0.9';
  const XMLNS_XHTML = 'http://www.w3.org/1999/xhtml';

  private $entityTypes;
  private $custom;
  private $links;
  private $generatingFrom;

  function __construct($from = 'form') {
    $this->links = array();
    $this->generatingFrom = $from;
  }

  public function setEntityTypes($entityTypes) {
    $this->entityTypes = is_array($entityTypes) ? $entityTypes : array();
  }

  public function setCustomLinks($custom) {
    $this->custom = is_array($custom) ? $custom : array();
  }

  /**
   * Adds all operations to the batch and starts it.
   */
  public function startBatch() {
    $batch = new Batch($this->generatingFrom);
    $batch->addOperations('custom_paths', $this->batchAddCustomPaths());
    $batch->addOperations('entity_types', $this->batchAddEntityTypePaths());
    $batch->start();
  }

  /**
   * Returns the custom path generating operation.
   *
   * @return array $operation.
   */
  private function batchAddCustomPaths() {
    $link_generator = new CustomLinkGenerator();
    return $link_generator->getCustomPaths($this->custom);
  }

  /**
   * Collects the entity path generating information from all simeple_sitemap
   * plugins to be added to the batch.
   *
   * @return array $operations.
   */
  private function batchAddEntityTypePaths() {

    $manager = \Drupal::service('plugin.manager.simple_sitemap');
    $plugins = $manager->getDefinitions();
    $operations = array();

    // Let all simple_sitemap plugins add their links to the sitemap.
    foreach ($plugins as $link_generator_plugin) {
      if (isset($this->entityTypes[$link_generator_plugin['id']])) {
        $instance = $manager->createInstance($link_generator_plugin['id']);
        foreach($this->entityTypes[$link_generator_plugin['id']] as $bundle => $bundle_settings) {
          if ($bundle_settings['index']) {
            $operation['query']['query'] = $instance->getQuery($bundle);
            $operation['query']['field_info'] = $instance->getQueryInfo()['field_info'];
            $operation['entity_info']['bundle_settings'] = $bundle_settings;
            $operation['entity_info']['bundle_name'] = $bundle;
            $operation['entity_info']['bundle_entity_type'] = $link_generator_plugin['id'];
            $operation['entity_info']['entity_type_name'] = !empty($link_generator_plugin['entity_type_name']) ? $link_generator_plugin['entity_type_name'] : '';
            $operations[] = $operation;
          }
        }
      }
    }
    return $operations;
  }

  /**
   * Wrapper method which takes links along with their options, lets other
   * modules alter the links and then generates and saves the sitemap.
   *
   * @param array $links
   *  All links with their multilingual versions and settings.
   */
  public static function generateSitemap($links, $remove_sitemap = FALSE) {
    // Invoke alter hook.
    \Drupal::moduleHandler()->alter('simple_sitemap_links', $links);
    $values = array(
      'id' => $remove_sitemap ? 1 : db_query('SELECT MAX(id) FROM {simple_sitemap}')->fetchField() + 1,
      'sitemap_string' => self::generateSitemapChunk($links),
      'sitemap_created' => REQUEST_TIME,
    );
    if ($remove_sitemap)
      db_truncate('simple_sitemap')->execute();
    db_insert('simple_sitemap')->fields($values)->execute();
  }

  /**
   * Generates and returns the sitemap index for all sitemap chunks.
   *
   * @param array $sitemap
   *  All sitemap chunks keyed by the chunk ID.
   *
   * @return string sitemap index
   */
  public function generateSitemapIndex($sitemap) {
    $writer = new XMLWriter();
    $writer->openMemory();
    $writer->setIndent(TRUE);
    $writer->startDocument(self::XML_VERSION, self::ENCODING);
    $writer->startElement('sitemapindex');
    $writer->writeAttribute('xmlns', self::XMLNS);

    foreach ($sitemap as $chunk_id => $chunk_data) {
      $writer->startElement('sitemap');
      $writer->writeElement('loc', $GLOBALS['base_url'] . '/sitemaps/'
        . $chunk_id . '/' . 'sitemap.xml');
      $writer->writeElement('lastmod', date_iso8601($chunk_data->sitemap_created));
      $writer->endElement();
    }
    $writer->endElement();
    $writer->endDocument();
    return $writer->outputMemory();
  }

  /**
   * Generates and returns a sitemap chunk.
   *
   * @param array $sitemap_links
   *  All links with their multilingual versions and settings.
   *
   * @return string sitemap chunk
   */
  private static function generateSitemapChunk($sitemap_links) {
    $default_language_id = Simplesitemap::getDefaultLangId();

    $writer = new XMLWriter();
    $writer->openMemory();
    $writer->setIndent(TRUE);
    $writer->startDocument(self::XML_VERSION, self::ENCODING);
    $writer->startElement('urlset');
    $writer->writeAttribute('xmlns', self::XMLNS);
    $writer->writeAttribute('xmlns:xhtml', self::XMLNS_XHTML);

    foreach ($sitemap_links as $link) {
      $writer->startElement('url');

      // Adding url to standard language.
      $writer->writeElement('loc', $link['urls'][$default_language_id]);

      // Adding alternate urls (other languages) if any.
      if (count($link['urls']) > 1) {
        foreach($link['urls'] as $language_id => $localised_url) {
          $writer->startElement('xhtml:link');
          $writer->writeAttribute('rel', 'alternate');
          $writer->writeAttribute('hreflang', $language_id);
          $writer->writeAttribute('href', $localised_url);
          $writer->endElement();
        }
      }

      // Add priority if any.
      if (isset($link['priority'])) {
        $writer->writeElement('priority', $link['priority']);
      }

      // Add lastmod if any.
      if (isset($link['lastmod'])) {
        $writer->writeElement('lastmod', $link['lastmod']);
      }
      $writer->endElement();
    }
    $writer->endElement();
    $writer->endDocument();
    return $writer->outputMemory();
  }
}

