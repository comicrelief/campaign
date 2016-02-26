<?php

/**
 * @file
 * Contains Drupal\search_api_page\Routing\SearchApiRoutes.
 */

namespace Drupal\search_api_page\Routing;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;

/**
 * Defines a route subscriber to register a url for serving search pages.
 */
class SearchApiPageRoutes implements ContainerInjectionInterface {

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new SearchApiRoutes object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   */
  public function __construct(EntityManagerInterface $entity_manager, LanguageManagerInterface $language_manager) {
    $this->entityManager = $entity_manager;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('language_manager')
    );
  }

  /**
   * Returns an array of route objects.
   *
   * @return \Symfony\Component\Routing\Route[]
   *   An array of route objects.
   */
  public function routes() {
    $routes = array();

    $is_multilingual = $this->languageManager->isMultilingual();

    /* @var $search_api_page \Drupal\search_api_page\SearchApiPageInterface */
    foreach ($this->entityManager->getStorage('search_api_page')->loadMultiple() as $search_api_page) {

      // Default path.
      $default_path = $search_api_page->getPath();

      // Loop over all languages so we can get the translated path (if any).
      foreach ($this->languageManager->getLanguages() as $language) {

        // Check if we are multilingual or not.
        if ($is_multilingual) {
          $path = $this->languageManager
            ->getLanguageConfigOverride($language->getId(), 'search_api_page.search_api_page.' . $search_api_page->id())
            ->get('path');
        }

        if (empty($path)) {
          $path = $default_path;
        }

        $args = [
          '_controller' => 'Drupal\search_api_page\Controller\SearchApiPageController::page',
          'search_api_page_name' => $search_api_page->id(),
        ];

        // Use clean urls or not.
        if ($search_api_page->getCleanUrl()) {
          $path .= '/{keys}';
          $args['keys'] = '';
        }

        $routes['search_api_page.' . $language->getId() . '.' . $search_api_page->id()] = new Route(
          $path,
          $args,
          array(
            '_permission' => 'view search api pages',
          )
        );

      }
    }

    return $routes;
  }

}
