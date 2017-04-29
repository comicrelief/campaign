<?php

/**
 * @file
 * Contains \Drupal\amp\Controller\ampNodeViewController.
 */

namespace Drupal\amp\Controller;

use Drupal\amp\Routing\AmpContext;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\node\Controller\NodeViewController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
/**
 * Class ampNodeViewController.
 *
 * @package Drupal\amp\Controller
 */
class ampNodeViewController extends NodeViewController {

  /**
   * The AMP context service.
   *
   * @var AmpContext $ampContext
   */
  protected $ampContext;

  /**
   * The config factory interface.
   *
   * @var ConfigFactoryInterface $configFactory
   */
  protected $configFactory;

  /**
   * The entity manager
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Creates an EntityViewController object.
   *
   * @param \Drupal\amp\Routing\AmpContext; $amp_context
   *   The AMP context.
   * @param \Drupal\Core\Config\ConfigFactoryInterface; $config_factory_interface
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(AmpContext $amp_context, ConfigFactoryInterface
  $config_factory_interface, EntityManagerInterface $entity_manager, RendererInterface $renderer) {
    parent::__construct($entity_manager, $renderer);
    $this->ampContext = $amp_context;
    $this->configFactory = $config_factory_interface;  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('router.amp_context'),
      $container->get('config.factory'),
      $container->get('entity.manager'),
      $container->get('renderer')
    );
  }

  public function warningsOn()
  {
    // First check the config if library warnings are on
    $amp_config = $this->configFactory->get('amp.settings');
    if ($amp_config->get('amp_library_warnings_display')) {
      return true;
    }

    // Then check the URL if library warnings are enabled
    /** @var Request $request */
    $request = \Drupal::request();
    $user_wants_amp_library_warnings = $request->get('warnfix');
    if (isset($user_wants_amp_library_warnings)) {
      return true;
    }

    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $node, $view_mode = 'full', $langcode = NULL) {

    // Only use the AMP view mode for content that is AMP enabled and in full
    // view mode.
    if ($this->ampContext->isAmpRoute() && $view_mode == 'full') {
      $build = parent::view($node, 'amp', $langcode);

      // Otherwise adding a ?amp query parameter at the end of URL will have no effect
      $build['#cache']['contexts'] = Cache::mergeContexts($build['#cache']['contexts'], ['url.query_args:amp']);
      // Otherwise adding a ?warnfix query parameter at the end of URL will have no effect
      $build['#cache']['contexts'] = Cache::mergeContexts($build['#cache']['contexts'], ['url.query_args:warnfix']);
      if ($this->warningsOn()) {
        $build['#cache']['keys'][] = 'amp-warnings-on';
      }
      else {
        $build['#cache']['keys'][] = 'amp-warnings-off';
      }
    }
    // Otherwise return the default view mode.
    else {
      $build = parent::view($node, $view_mode, $langcode);
    }

    foreach ($node->uriRelationships() as $rel) {
      // Set the node path as the canonical URL to prevent duplicate content.
      $build['#attached']['html_head_link'][] = array(
        array(
          'rel' => $rel,
          'href' => $node->url($rel),
        ),
        TRUE,
      );

      if ($rel == 'canonical') {
        // Set the non-aliased canonical path as a default shortlink.
        $build['#attached']['html_head_link'][] = array(
          array(
            'rel' => 'shortlink',
            'href' => $node->url($rel, array('alias' => TRUE)),
          ),
          TRUE,
        );
      }
    }

    return $build;
  }
}
