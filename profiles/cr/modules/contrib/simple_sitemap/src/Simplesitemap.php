<?php
/**
 * @file
 * Contains \Drupal\simple_sitemap\Simplesitemap.
 */

namespace Drupal\simple_sitemap;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityTypeInterface;

/**
 * Simplesitemap class.
 *
 * Main module class.
 */
class Simplesitemap {

  private $config;
  private $sitemap;

  /**
   * Simplesitemap constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory from the container.
   */
  function __construct(ConfigFactoryInterface $config_factory) {
    $this->config = $config_factory->get('simple_sitemap.settings');
    $this->sitemap = \Drupal::service('database')->query("SELECT * FROM {simple_sitemap}")->fetchAllAssoc('id');
  }

  /**
   * Gets a specific sitemap configuration from the configuration storage.
   *
   * @param string $key
   *  Configuration key, like 'entity_types'.
   * @return mixed
   *  The requested configuration.
   */
  public function getConfig($key) {
    return $this->config->get($key);
  }

  /**
   * Saves a specific sitemap configuration to db.
   *
   * @param string $key
   *  Configuration key, like 'entity_types'.
   * @param mixed $value
   *  The configuration to be saved.
   */
  public function saveConfig($key, $value) {
    \Drupal::service('config.factory')->getEditable('simple_sitemap.settings')
      ->set($key, $value)->save();
  }

  /**
   * Returns the whole sitemap, a requested sitemap chunk,
   * or the sitemap index file.
   *
   * @param int $sitemap_id
   *
   * @return string $sitemap
   *  If no sitemap id provided, either a sitemap index is returned, or the
   *  whole sitemap, if the amount of links does not exceed the max links setting.
   *  If a sitemap id is provided, a sitemap chunk is returned.
   */
  public function getSitemap($sitemap_id = NULL) {
    if (is_null($sitemap_id) || !isset($this->sitemap[$sitemap_id])) {

      // Return sitemap index, if there are multiple sitemap chunks.
      if (count($this->sitemap) > 1) {
        return $this->getSitemapIndex();
      }

      // Return sitemap if there is only one chunk.
      else {
        if (isset($this->sitemap[1])) {
          return $this->sitemap[1]->sitemap_string;
        }
        return FALSE;
      }
    }
    // Return specific sitemap chunk.
    else {
      return $this->sitemap[$sitemap_id]->sitemap_string;
    }
  }

  /**
   * Generates the sitemap for all languages and saves it to the db.
   *
   * @param string $from
   *  Can be 'form', 'cron', or 'drush'. This decides how to the batch process
   *  is to be run.
   */
  public function generateSitemap($from = 'form') {
    $generator = new SitemapGenerator($from);
    $generator->setCustomLinks($this->getConfig('custom'));
    $generator->setEntityTypes($this->getConfig('entity_types'));
    $generator->startBatch();
  }

  /**
   * Generates and returns the sitemap index as string.
   *
   * @return string
   *  The sitemap index.
   */
  private function getSitemapIndex() {
    $generator = new SitemapGenerator();
    return $generator->generateSitemapIndex($this->sitemap);
  }

  /**
   * Gets a specific sitemap setting.
   *
   * @param string $name
   *  Name of the setting, like 'max_links'.
   *
   * @return mixed
   *  The current setting from db or FALSE if setting does not exist.
   */
  public function getSetting($name) {
    $settings = $this->getConfig('settings');
    return isset($settings[$name]) ? $settings[$name] : FALSE;
  }

  /**
   * Saves a specific sitemap setting to db.
   *
   * @param $name
   *  Setting name, like 'max_links'.
   * @param $setting
   *  The setting to be saved.
   */
  public function saveSetting($name, $setting) {
    $settings = $this->getConfig('settings');
    $settings[$name] = $setting;
    $this->saveConfig('settings', $settings);
  }

  /**
   * Returns a 'time ago' string of last timestamp generation.
   *
   * @return mixed
   *  Formatted timestamp of last sitemap generation, otherwise FALSE.
   */
  public function getGeneratedAgo() {
    if (isset($this->sitemap[1]->sitemap_created)) {
      return \Drupal::service('date.formatter')
        ->formatInterval(REQUEST_TIME - $this->sitemap[1]->sitemap_created);
    }
    return FALSE;
  }

  public static function getDefaultLangId() {
    return \Drupal::languageManager()->getDefaultLanguage()->getId();
  }

  /**
   * Returns objects of entity types that can be indexed by the sitemap.
   *
   * @return array
   *  Objects of entity types that can be indexed by the sitemap.
   */
  public static function getSitemapEntityTypes() {
    $entity_types = \Drupal::entityTypeManager()->getDefinitions();

    foreach ($entity_types as $entity_type_id => $entity_type) {
      if (!$entity_type instanceof ContentEntityTypeInterface || !method_exists($entity_type, 'getBundleEntityType')) {
        unset($entity_types[$entity_type_id]);
        continue;
      }
    }
    return $entity_types;
  }

  public static function entityTypeIsAtomic($entity_type_id) { //todo: make it work with entity object as well
    if ($entity_type_id == 'menu_link_content') // Menu fix.
      return FALSE;
    $sitemap_entity_types = self::getSitemapEntityTypes();
    if (isset($sitemap_entity_types[$entity_type_id])) {
      $entity_type = $sitemap_entity_types[$entity_type_id];
      if (empty($entity_type->getBundleEntityType())) {
        return TRUE;
      }
      return FALSE;
    }
    return FALSE; //todo: throw exception
  }
}
