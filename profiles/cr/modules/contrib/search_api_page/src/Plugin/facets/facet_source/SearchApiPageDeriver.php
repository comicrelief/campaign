<?php

/**
 * @file
 * Contains \Drupal\search_api_page\Plugin\facets\facet_source\SearchApiPageDeriver.
 */

namespace Drupal\search_api_page\Plugin\facets\facet_source;

use Drupal\Core\Plugin\PluginBase;
use Drupal\facets\FacetSource\FacetSourceDeriverBase;


/**
 * Derives a facet source plugin definition for every search api page.
 *
 * The definition of this plugin happens in facet_source\SearchApiPage, in this
 * deriver class we're actually getting all possible pages and creating plugins
 * for each of them.
 *
 * @see \Drupal\search_api_page\Plugin\facets\facet_source\SearchApiPage
 */
class SearchApiPageDeriver extends FacetSourceDeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $base_plugin_id = $base_plugin_definition['id'];

    if (!isset($this->derivatives[$base_plugin_id])) {

      $plugin_derivatives = [];

      /* @var \Drupal\Core\Entity\EntityStorageInterface $page_storage */
      $page_storage = $this->entityTypeManager->getStorage('search_api_page');
      $all_pages = $page_storage->loadMultiple();

      /* @var \Drupal\search_api_page\Entity\SearchApiPage */
      foreach ($all_pages as $page) {
        $machine_name = $page->id();

        // Add plugin derivatives, they have 'search_api_page' as a special key
        // in them, because of this, there needs to happen less explode() magic
        // in the plugin class.
        $plugin_derivatives[$machine_name] = [
          'id' => $base_plugin_id . PluginBase::DERIVATIVE_SEPARATOR . $machine_name,
          'label' => $this->t('Search api page: %page_name', ['%page_name' => $page->label()]),
          'description' => $this->t('Provides a facet source.'),
          'search_api_page' => $page->id(),
        ] + $base_plugin_definition;

        $sources[] = $this->t('Search api page: %page_name', ['%page_name' => $page->label()]);
      }
      uasort($plugin_derivatives, array($this, 'compareDerivatives'));

      $this->derivatives[$base_plugin_id] = $plugin_derivatives;
    }
    return $this->derivatives[$base_plugin_id];
  }

}
