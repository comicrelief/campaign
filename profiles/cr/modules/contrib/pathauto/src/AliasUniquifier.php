<?php

/**
 * @file
 * Contains Drupal\pathauto\AliasUniquifier
 */

namespace Drupal\pathauto;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Path\AliasStorageInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

/**
 * Provides a utility for creating a unique path alias.
 */
class AliasUniquifier implements AliasUniquifierInterface {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The alias storage helper.
   *
   * @var \Drupal\pathauto\AliasStorageHelperInterface
   */
  protected $aliasStorageHelper;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The url matcher service.
   *
   * @var \Symfony\Component\Routing\Matcher\UrlMatcherInterface
   */
  protected $urlMatcher;

  /**
   * The alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Stores the last matching route name.
   *
   * Used to prevent a loop if the same route matches a given pattern.
   *
   * @var
   */
  protected $lastRouteName;

  /**
   * Creates a new AliasUniquifier.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\pathauto\AliasStorageHelperInterface $alias_storage_helper
   *   The alias storage helper.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Symfony\Component\Routing\Matcher\UrlMatcherInterface $url_matcher
   *   The url matcher service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, AliasStorageHelperInterface $alias_storage_helper, ModuleHandlerInterface $module_handler, UrlMatcherInterface $url_matcher, AliasManagerInterface $alias_manager) {
    $this->configFactory = $config_factory;
    $this->aliasStorageHelper = $alias_storage_helper;
    $this->moduleHandler = $module_handler;
    $this->urlMatcher = $url_matcher;
    $this->aliasManager = $alias_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function uniquify(&$alias, $source, $langcode) {
    $config = $this->configFactory->get('pathauto.settings');

    if (!$this->isReserved($alias, $source, $langcode)) {
      return;
    }

    // If the alias already exists, generate a new, hopefully unique, variant.
    $maxlength = min($config->get('max_length'), $this->aliasStorageHelper->getAliasSchemaMaxlength());
    $separator = $config->get('separator');
    $original_alias = $alias;

    $i = 0;
    do {
      // Append an incrementing numeric suffix until we find a unique alias.
      $unique_suffix = $separator . $i;
      $alias = Unicode::truncate($original_alias, $maxlength - Unicode::strlen($unique_suffix), TRUE) . $unique_suffix;
      $i++;
    } while ($this->isReserved($alias, $source, $langcode));
  }

  /**
   * {@inheritdoc}
   */
  public function isReserved($alias, $source, $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED) {
    // Check if this alias already exists.
    if ($existing_source = $this->aliasManager->getPathByAlias($alias, $langcode)) {
      if ($existing_source != $alias) {
        // If it is an alias for the provided source, it is allowed to keep using
        // it. If not, then it is reserved.
        return $existing_source != $source;
      }

    }

    // Then check if there is a route with the same path.
    if ($this->isRoute($alias)) {
      return TRUE;
    }
    // Finally check if any other modules have reserved the alias.
    $args = array(
      $alias,
      $source,
      $langcode,
    );
    $implementations = $this->moduleHandler->getImplementations('pathauto_is_alias_reserved');
    foreach ($implementations as $module) {

      $result = $this->moduleHandler->invoke($module, 'pathauto_is_alias_reserved', $args);

      if (!empty($result)) {
        // As soon as the first module says that an alias is in fact reserved,
        // then there is no point in checking the rest of the modules.
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Verify if the given path is a valid route.
   *
   * @param string $path
   *   A string containing a relative path.
   *
   * @return bool
   *   TRUE if the path already exists.
   *
   * @throws \InvalidArgumentException
   */
  public function isRoute($path) {
    if (is_file(DRUPAL_ROOT . '/' . $path) || is_dir(DRUPAL_ROOT . '/' . $path)) {
      // Do not allow existing files or directories to get assigned an automatic
      // alias. Note that we do not need to use is_link() to check for symbolic
      // links since this returns TRUE for either is_file() or is_dir() already.
      return TRUE;
    }

    try {
      $route = $this->urlMatcher->match($path);

      if ($route['_route'] == $this->lastRouteName) {
        throw new \InvalidArgumentException('The given alias pattern (' . $path . ') always matches the route ' . $this->lastRouteName);
      }

      $this->lastRouteName = $route['_route'];
      return TRUE;
    }
    catch (ResourceNotFoundException $e) {
      $this->lastRouteName = NULL;
      return FALSE;
    }
  }

}
