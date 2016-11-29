<?php

namespace Drupal\twig_tweak;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Utility\Token;
use Drupal\image\Entity\ImageStyle;

/**
 * Twig extension with some useful functions and filters.
 */
class TwigExtension extends \Twig_Extension {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * TwigExtension constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, Token $token, ConfigFactoryInterface $config_factory, RouteMatchInterface $route_match) {
    $this->entityTypeManager = $entity_type_manager;
    $this->token = $token;
    $this->configFactory = $config_factory;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('drupal_view', 'views_embed_view'),
      new \Twig_SimpleFunction('drupal_block', [$this, 'drupalBlock']),
      new \Twig_SimpleFunction('drupal_token', [$this, 'drupalToken']),
      new \Twig_SimpleFunction('drupal_entity', [$this, 'drupalEntity']),
      new \Twig_SimpleFunction('drupal_field', [$this, 'drupalField']),
      new \Twig_SimpleFunction('drupal_config', [$this, 'drupalConfig']),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFilters() {
    $filters = [
      new \Twig_SimpleFilter('token_replace', [$this, 'tokenReplaceFilter']),
      new \Twig_SimpleFilter('preg_replace', [$this, 'pregReplaceFilter']),
      new \Twig_SimpleFilter('image_style', [$this, 'imageStyle']),
    ];
    // PHP filter should be enabled in settings.php file.
    if (Settings::get('twig_tweak_enable_php_filter')) {
      $filters[] = new \Twig_SimpleFilter('php', [$this, 'phpFilter']);
    }
    return $filters;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'twig_tweak';
  }

  /**
   * Builds the render array for the provided block.
   *
   * @param mixed $id
   *   The ID of the block to render.
   *
   * @return null|array
   *   A render array for the block or NULL if the block does not exist.
   */
  public function drupalBlock($id) {
    $block = $this->entityTypeManager->getStorage('block')->load($id);
    return $block ?
      $this->entityTypeManager->getViewBuilder('block')->view($block) : '';
  }

  /**
   * Replaces a given tokens with appropriate value.
   *
   * @param string $token
   *   A replaceable token.
   *
   * @return string
   *   The token value.
   */
  public function drupalToken($token) {
    return $this->token->replace("[$token]");
  }

  /**
   * Returns the render array for an entity.
   *
   * @param string $entity_type
   *   The entity type.
   * @param mixed $id
   *   The ID of the entity to render.
   * @param string $view_mode
   *   (optional) The view mode that should be used to render the entity.
   * @param string $langcode
   *   (optional) For which language the entity should be rendered, defaults to
   *   the current content language.
   *
   * @return null|array
   *   A render array for the entity or NULL if the entity does not exist.
   */
  public function drupalEntity($entity_type, $id = NULL, $view_mode = NULL, $langcode = NULL) {
    $entity = $id ?
      $this->entityTypeManager->getStorage($entity_type)->load($id) :
      $this->routeMatch->getParameter($entity_type);
    if ($entity) {
      $render_controller = $this->entityTypeManager->getViewBuilder($entity_type);
      return $render_controller->view($entity, $view_mode, $langcode);
    }
    return NULL;
  }

  /**
   * Returns the render array for a single entity field.
   *
   * @param string $field_name
   *   The field name.
   * @param string $entity_type
   *   The entity type.
   * @param mixed $id
   *   The ID of the entity to render.
   * @param string $view_mode
   *   (optional) The view mode that should be used to render the entity.
   *
   * @return null|array
   *   A render array for the field or NULL if the value does not exist.
   */
  public function drupalField($field_name, $entity_type, $id = NULL, $view_mode = 'default') {
    $entity = $id ?
      $this->entityTypeManager->getStorage($entity_type)->load($id) :
      $this->routeMatch->getParameter($entity_type);
    if (isset($entity->{$field_name})) {
      return $entity->{$field_name}->view($view_mode);
    }
    return NULL;
  }

  /**
   * Gets data from this configuration.
   *
   * @param string $name
   *   The name of the configuration object to construct.
   * @param string $key
   *   A string that maps to a key within the configuration data.
   *
   * @return mixed
   *   The data that was requested.
   */
  public function drupalConfig($name, $key) {
    return $this->configFactory->get($name)->get($key);
  }

  /**
   * Evaluates a string of PHP code.
   *
   * @param string $code
   *   Valid PHP code to be evaluated.
   *
   * @return mixed
   *   The eval() result.
   */
  public function phpFilter($code) {
    ob_start();
    print eval($code);
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
  }

  /**
   * Replaces all tokens in a given string with appropriate values.
   *
   * @param string $text
   *   An HTML string containing replaceable tokens.
   *
   * @return string
   *   The entered HTML text with tokens replaced.
   */
  public function tokenReplaceFilter($text) {
    return $this->token->replace($text);
  }

  /**
   * Performs a regular expression search and replace.
   *
   * @param string $text
   *   The text to search and replace.
   * @param string $pattern
   *   The pattern to search for.
   * @param string $replacement
   *   The string to replace.
   *
   * @return string
   *   The new text if matches are found, otherwise unchanged text.
   */
  public function pregReplaceFilter($text, $pattern, $replacement) {
    return preg_replace("/$pattern/", $replacement, $text);
  }

  /**
   * Returns the URL of this image derivative for an original image path or URI.
   *
   * @param string $path
   *   The path or URI to the original image.
   * @param string $style
   *   The image style.
   *
   * @return string
   *   The absolute URL where a style image can be downloaded, suitable for use
   *   in an <img> tag. Requesting the URL will cause the image to be created.
   */
  public function imageStyle($path, $style) {
    return ImageStyle::load($style)->buildUrl($path);
  }

}
