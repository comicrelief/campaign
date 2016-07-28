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
   * Collects entity metadata for entities that are set to be indexed
   * and returns a batch-ready operation.
   *
   * @return array $operations.
   */
  private function batchAddEntityTypePaths() {
    $operations = [];
    $sitemap_entity_types = Simplesitemap::getSitemapEntityTypes();
    foreach($this->entityTypes as $entity_type_name => $bundles) {
      if (isset($sitemap_entity_types[$entity_type_name])) {
        $keys = $sitemap_entity_types[$entity_type_name]->getKeys();
        $keys['bundle'] = $entity_type_name == 'menu_link_content' ? 'menu_name' : $keys['bundle']; // Menu fix.
        foreach($bundles as $bundle_name => $bundle_settings) {
          if ($bundle_settings['index']) {
            $operations[] = [
              'entity_info' => [
                'bundle_settings' => $bundle_settings,
                'bundle_name' => $bundle_name,
                'entity_type_name' => $entity_type_name,
                'keys' => $keys,
              ],
            ];
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
   * @param bool $remove_sitemap
   *  Remove old sitemap from database before inserting the new one.
   */
  public static function generateSitemap($links, $remove_sitemap = FALSE) {
    // Invoke alter hook.
    \Drupal::moduleHandler()->alter('simple_sitemap_links', $links);
    $values = array(
      'id' => $remove_sitemap ? 1 : \Drupal::service('database')->query('SELECT MAX(id) FROM {simple_sitemap}')->fetchField() + 1,
      'sitemap_string' => self::generateSitemapChunk($links),
      'sitemap_created' => REQUEST_TIME,
    );
    if ($remove_sitemap) {
      \Drupal::service('database')->truncate('simple_sitemap')->execute();
    }
    \Drupal::service('database')->insert('simple_sitemap')->fields($values)->execute();
  }

  /**
   * Generates and returns the sitemap index for all sitemap chunks.
   *
   * @param array $sitemap_chunks
   *  All sitemap chunks keyed by the chunk ID.
   *
   * @return string sitemap index
   */
  public function generateSitemapIndex($sitemap_chunks) {
    $writer = new XMLWriter();
    $writer->openMemory();
    $writer->setIndent(TRUE);
    $writer->startDocument(self::XML_VERSION, self::ENCODING);
    $writer->startElement('sitemapindex');
    $writer->writeAttribute('xmlns', self::XMLNS);

    foreach ($sitemap_chunks as $chunk_id => $chunk_data) {
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

