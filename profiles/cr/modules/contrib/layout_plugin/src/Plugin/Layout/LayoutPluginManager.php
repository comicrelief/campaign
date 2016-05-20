<?php

/**
 * @file
 * Contains \Drupal\layout_plugin\Plugin\Layout\LayoutPluginManager.
 */

namespace Drupal\layout_plugin\Plugin\Layout;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\YamlDiscoveryDecorator;

/**
 * Plugin type manager for all layouts.
 */
class LayoutPluginManager extends DefaultPluginManager implements LayoutPluginManagerInterface {


  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Constructs a LayoutPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handle to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler) {
    $plugin_interface = 'Drupal\layout_plugin\Plugin\Layout\LayoutInterface';
    $plugin_definition_annotation_name = 'Drupal\layout_plugin\Annotation\Layout';
    parent::__construct("Plugin/Layout", $namespaces, $module_handler, $plugin_interface, $plugin_definition_annotation_name);
    $discovery = $this->getDiscovery();
    $this->discovery = new YamlDiscoveryDecorator($discovery, 'layouts', $module_handler->getModuleDirectories() + $theme_handler->getThemeDirectories());
    $this->themeHandler = $theme_handler;

    $this->defaults += array(
      'type' => 'page',
      // Used for plugins defined in layouts.yml that do not specify a class
      // themselves.
      'class' => 'Drupal\layout_plugin\Plugin\Layout\LayoutDefault',
    );

    $this->setCacheBackend($cache_backend, 'layout');
    $this->alterInfo('layout');
  }

  /**
   * {@inheritdoc}
   */
  protected function providerExists($provider) {
    return $this->moduleHandler->moduleExists($provider) || $this->themeHandler->themeExists($provider);
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);

    // Add the module or theme path to the 'path'.
    if ($this->moduleHandler->moduleExists($definition['provider'])) {
      $definition['provider_type'] = 'module';
      $base_path = $this->moduleHandler->getModule($definition['provider'])->getPath();
    }
    elseif ($this->themeHandler->themeExists($definition['provider'])) {
      $definition['provider_type'] = 'theme';
      $base_path = $this->themeHandler->getTheme($definition['provider'])->getPath();
    }
    else {
      $base_path = '';
    }
    $definition['path'] = !empty($definition['path']) ? $base_path . '/' . $definition['path'] : $base_path;

    // Add the path to the icon filename.
    if (!empty($definition['icon'])) {
      $definition['icon'] = $definition['path'] . '/' . $definition['icon'];
    }

    // If 'template' is set, then we'll derive 'template_path' and 'theme'.
    if (!empty($definition['template'])) {
      $template_parts = explode('/', $definition['template']);

      $definition['template'] = array_pop($template_parts);
      $definition['theme'] = strtr($definition['template'], '-', '_');
      $definition['template_path'] = $definition['path'];
      if (count($template_parts) > 0) {
        $definition['template_path'] .= '/' . implode('/', $template_parts);
      }
    }

    // If 'css' is set, then we'll derive 'library'.
    if (!empty($definition['css'])) {
      $definition['css'] = $definition['path'] . '/' . $definition['css'];
      $definition['library'] = 'layout_plugin/' . $plugin_id;
    }

    // Generate the 'region_names' key from the 'regions' key.
    $definition['region_names'] = array();
    if (!empty($definition['regions']) && is_array($definition['regions'])) {
      foreach ($definition['regions'] as $region_id => $region_definition) {
        $definition['region_names'][$region_id] = $region_definition['label'];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getLayoutOptions(array $params = []) {
    $group_by_category = !empty($params['group_by_category']);
    $plugins = $this->getDefinitions();

    // Sort the plugins first by category, then by label.
    $options = array();
    foreach ($plugins as $id => $plugin) {
      if ($group_by_category) {
        $category = isset($plugin['category']) ? (string) $plugin['category'] : 'default';
        if (!isset($options[$category])) {
          $options[$category] = array();
        }
        $options[$category][$id] = $plugin['label'];
      }
      else {
        $options[$id] = $plugin['label'];
      }
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getThemeImplementations() {
    $plugins = $this->getDefinitions();

    $theme_registry = [];
    foreach ($plugins as $id => $definition) {
      if (!empty($definition['template']) && !empty($definition['theme'])) {
        $theme_registry[$definition['theme']] = [
          'render element' => 'content',
          'template' => $definition['template'],
          'path' => $definition['template_path'],
        ];
      }
    }

    return $theme_registry;
  }

  /**
   * {@inheritdoc}
   */
  public function alterThemeImplementations(array &$theme_registry) {
    $plugins = $this->getDefinitions();

    // Find all the theme hooks which are for automatically registered templates
    // (we ignore manually set theme hooks because we don't know how they were
    // registered).
    $layout_theme_hooks = [];
    foreach ($plugins as $id => $definition) {
      if (!empty($definition['template']) && !empty($definition['theme']) && isset($theme_registry[$definition['theme']])) {
        $layout_theme_hooks[] = $definition['theme'];
      }
    }

    // Go through the theme registry looking for our theme hooks and any
    // suggestions based on them.
    foreach ($theme_registry as $theme_hook => &$info) {
      if (in_array($theme_hook, $layout_theme_hooks) || (!empty($info['base hook']) && in_array($info['base hook'], $layout_theme_hooks))) {
        // If 'template_preprocess' is included, we want to put our preprocess
        // after to not mess up the expectation that 'template_process' always
        // runs first.
        if (($index = array_search('template_preprocess', $info['preprocess functions'])) !== FALSE) {
          $index++;
        }
        else {
          // Otherwise, put our preprocess function first.
          $index = 0;
        }

        array_splice($info['preprocess functions'], $index, 0, '_layout_plugin_preprocess_layout');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraryInfo() {
    $plugins = $this->getDefinitions();

    $library_info = [];
    foreach ($plugins as $id => $definition) {
      if (!empty($definition['css']) && !empty($definition['library'])) {
        list ($library_module, $library_name) = explode('/', $definition['library']);

        // Make sure the library is from layout_plugin.
        if ($library_module != 'layout_plugin') {
          continue;
        }

        $library_info[$library_name] = [
          // @todo: Should be the version of the provider module or theme.
          'version' => 'VERSION',
          'css' => [
            'theme' => [
              '/' . $definition['css'] => [],
            ],
          ],
        ];
      }
    }

    return $library_info;
  }

}
