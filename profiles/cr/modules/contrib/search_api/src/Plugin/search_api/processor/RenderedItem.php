<?php

namespace Drupal\search_api\Plugin\search_api\processor;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Session\UserSession;
use Drupal\Core\Theme\ThemeInitializationInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Plugin\search_api\processor\Property\RenderedItemProperty;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Adds an additional field containing the rendered item.
 *
 * @SearchApiProcessor(
 *   id = "rendered_item",
 *   label = @Translation("Rendered item"),
 *   description = @Translation("Adds an additional field containing the rendered item as it would look when viewed."),
 *   stages = {
 *     "add_properties" = 0,
 *     "pre_index_save" = -10,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class RenderedItem extends ProcessorPluginBase {

  /**
   * The current_user service used by this plugin.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface|null
   */
  protected $currentUser;

  /**
   * The renderer to use.
   *
   * @var \Drupal\Core\Render\RendererInterface|null
   */
  protected $renderer;

  /**
   * The logger to use for log messages.
   *
   * @var \Psr\Log\LoggerInterface|null
   */
  protected $logger;

  /**
   * Theme manager service.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * Theme initialization service.
   *
   * @var \Drupal\Core\Theme\ThemeInitializationInterface
   */
  protected $themeInitialization;

  /**
   * Theme settings config.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var static $plugin */
    $plugin = parent::create($container, $configuration, $plugin_id, $plugin_definition);

    $plugin->setCurrentUser($container->get('current_user'));
    $plugin->setRenderer($container->get('renderer'));
    $plugin->setLogger($container->get('logger.channel.search_api'));
    $plugin->setThemeManager($container->get('theme.manager'));
    $plugin->setThemeInitializer($container->get('theme.initialization'));
    $plugin->setConfigFactory($container->get('config.factory'));

    return $plugin;
  }

  /**
   * Retrieves the current user.
   *
   * @return \Drupal\Core\Session\AccountProxyInterface
   *   The current user.
   */
  public function getCurrentUser() {
    return $this->currentUser ?: \Drupal::currentUser();
  }

  /**
   * Sets the current user.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   *
   * @return $this
   */
  public function setCurrentUser(AccountProxyInterface $current_user) {
    $this->currentUser = $current_user;
    return $this;
  }

  /**
   * Retrieves the renderer.
   *
   * @return \Drupal\Core\Render\RendererInterface
   *   The renderer.
   */
  public function getRenderer() {
    return $this->renderer ?: \Drupal::service('renderer');
  }

  /**
   * Sets the renderer.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The new renderer.
   *
   * @return $this
   */
  public function setRenderer(RendererInterface $renderer) {
    $this->renderer = $renderer;
    return $this;
  }

  /**
   * Retrieves the logger to use for log messages.
   *
   * @return \Psr\Log\LoggerInterface
   *   The logger to use.
   */
  public function getLogger() {
    return $this->logger ?: \Drupal::service('logger.channel.search_api');
  }

  /**
   * Sets the logger to use for log messages.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   The new logger.
   *
   * @return $this
   */
  public function setLogger(LoggerInterface $logger) {
    $this->logger = $logger;
    return $this;
  }

  /**
   * Retrieves the theme manager.
   *
   * @return \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The theme manager.
   */
  protected function getThemeManager() {
    return $this->themeManager ?: \Drupal::theme();
  }

  /**
   * Sets the theme manager.
   *
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The theme manager.
   *
   * @return $this
   */
  protected function setThemeManager(ThemeManagerInterface $theme_manager) {
    $this->themeManager = $theme_manager;
    return $this;
  }

  /**
   * Retrieves the theme initialization service.
   *
   * @return \Drupal\Core\Theme\ThemeInitializationInterface $theme_initialization
   *   The theme initialization service.
   */
  protected function getThemeInitializer() {
    return $this->themeInitialization ?: \Drupal::service('theme.initialization');
  }

  /**
   * Sets the theme initialization service.
   *
   * @param \Drupal\Core\Theme\ThemeInitializationInterface $theme_initialization
   *   The theme initialization service.
   *
   * @return $this
   */
  protected function setThemeInitializer(ThemeInitializationInterface $theme_initialization) {
    $this->themeInitialization = $theme_initialization;
    return $this;
  }

  /**
   * Retrieves the config factory service.
   *
   * @return \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  protected function getConfigFactory() {
    return $this->configFactory ?: \Drupal::configFactory();
  }

  /**
   * Sets the config factory service.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   *
   * @return $this
   */
  protected function setConfigFactory(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
    return $this;
  }

  // @todo Add a supportsIndex() implementation that checks whether there is
  //   actually any datasource present which supports viewing.

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = array();

    if (!$datasource) {
      $definition = array(
        'type' => 'text',
        'label' => $this->t('Rendered HTML output'),
        'description' => $this->t('The complete HTML which would be displayed when viewing the item'),
        'processor_id' => $this->getPluginId(),
      );
      $properties['rendered_item'] = new RenderedItemProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $original_user = $this->currentUser->getAccount();

    // Switch to the default theme in case the admin theme is enabled.
    $active_theme = $this->getThemeManager()->getActiveTheme();
    $default_theme = $this->getConfigFactory()
      ->get('system.theme')
      ->get('default');
    $default_theme = $this->getThemeInitializer()
      ->getActiveThemeByName($default_theme);
    $this->getThemeManager()->setActiveTheme($default_theme);

    // Count of items that don't have a view mode.
    $unset_view_modes = 0;

    foreach ($this->filterForPropertyPath($item->getFields(), 'rendered_item') as $field) {
      $configuration = $field->getConfiguration();

      // Change the current user to our dummy implementation to ensure we are
      // using the configured roles.
      $this->currentUser->setAccount(new UserSession(array('roles' => $configuration['roles'])));

      $datasource_id = $item->getDatasourceId();
      $datasource = $item->getDatasource();
      $bundle = $datasource->getItemBundle($item->getOriginalObject());
      // When no view mode has been set for the bundle, or it has been set to
      // "Don't include the rendered item", skip this item.
      if (empty($configuration['view_mode'][$datasource_id][$bundle])) {
        // If it was really not set, also notify the user through the log.
        if (!isset($configuration['view_mode'][$datasource_id][$bundle])) {
          ++$unset_view_modes;
        }
        continue;
      }
      else {
        $view_mode = (string) $configuration['view_mode'][$datasource_id][$bundle];
      }

      $build = $datasource->viewItem($item->getOriginalObject(), $view_mode);
      $value = (string) $this->getRenderer()->renderPlain($build);
      if ($value) {
        $field->addValue($value);
      }
    }

    // Restore the original user.
    $this->currentUser->setAccount($original_user);
    // Restore the original theme.
    $this->getThemeManager()->setActiveTheme($active_theme);

    if ($unset_view_modes > 0) {
      $context = array(
        '%index' => $this->index->label(),
        '%processor' => $this->label(),
        '@count' => $unset_view_modes,
      );
      $this->getLogger()->warning('Warning: While indexing items on search index %index, @count item(s) did not have a view mode configured for one or more "Rendered item" fields.', $context);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $this->dependencies = parent::calculateDependencies();

    $fields = $this->index->getFieldsByDatasource(NULL);
    $fields = $this->filterForPropertyPath($fields, 'rendered_item');
    foreach ($fields as $field) {
      $view_modes = $field->getConfiguration()['view_mode'];
      foreach ($this->index->getDatasources() as $datasource_id => $datasource) {
        if (($entity_type_id = $datasource->getEntityTypeId()) && !empty($view_modes[$datasource_id])) {
          foreach ($view_modes[$datasource_id] as $view_mode) {
            if ($view_mode) {
              /** @var \Drupal\Core\Entity\EntityViewModeInterface $view_mode_entity */
              $view_mode_entity = EntityViewMode::load($entity_type_id . '.' . $view_mode);
              if ($view_mode_entity) {
                $this->addDependency($view_mode_entity->getConfigDependencyKey(), $view_mode_entity->getConfigDependencyName());
              }
            }
          }
        }
      }
    }

    return $this->dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function onDependencyRemoval(array $dependencies) {
    // All dependencies of this processor are entity view modes, so we go
    // through all of the index's fields using our property and remove the
    // settings for all datasources or bundles which were set to one of the
    // removed view modes. This will always result in the removal of all those
    // dependencies.
    // The code is highly similar to calculateDependencies(), only that we
    // remove the setting (if necessary) instead of adding a dependency.
    $fields = $this->index->getFieldsByDatasource(NULL);
    $fields = $this->filterForPropertyPath($fields, 'rendered_item');
    foreach ($fields as $field) {
      $field_config = $field->getConfiguration();
      $view_modes = $field_config['view_mode'];
      foreach ($this->index->getDatasources() as $datasource_id => $datasource) {
        if (!empty($view_modes[$datasource_id]) && ($entity_type_id = $datasource->getEntityTypeId())) {
          foreach ($view_modes[$datasource_id] as $bundle => $view_mode_id) {
            if ($view_mode_id) {
              /** @var \Drupal\Core\Entity\EntityViewModeInterface $view_mode */
              $view_mode = EntityViewMode::load($entity_type_id . '.' . $view_mode_id);
              if ($view_mode) {
                $dependency_key = $view_mode->getConfigDependencyKey();
                $dependency_name = $view_mode->getConfigDependencyName();
                if (!empty($dependencies[$dependency_key][$dependency_name])) {
                  unset($view_modes[$datasource_id][$bundle]);
                }
              }
            }
          }
        }
      }
      $field_config['view_mode'] = $view_modes;
      $field->setConfiguration($field_config);
    }

    return TRUE;
  }

}
