<?php

namespace Drupal\Tests\diff\Unit;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\diff\VisualDiffThemeNegotiator;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class VisualDiffThemeNegotiatorTest
 *
 * @coversDefaultClass \Drupal\diff\VisualDiffThemeNegotiator
 * @group diff
 */
class VisualDiffThemeNegotiatorTest extends UnitTestCase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The route admin context to determine whether a route is an admin one.
   *
   * @var \Drupal\Core\Routing\AdminContext
   */
  protected $adminContext;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->user = $this->prophesize(AccountInterface::class);
    $this->configFactory = $this->prophesize(ConfigFactoryInterface::class);
    $this->entityManager = $this->prophesize(EntityManagerInterface::class);
    $this->adminContext = $this->prophesize(AdminContext::class);
  }

  /**
   * Tests if applies method returns true.
   *
   * Check the value returned by the applies method when the theme used for
   * visual inline plugin is "Standard".
   */
  public function testNegotiator() {
    // Create $theme using mocked class.
    $theme = new VisualDiffThemeNegotiator($this->user->reveal(),
      $this->configFactory->reveal(),
      $this->entityManager->reveal(),
      $this->adminContext->reveal());

    // Mocking $route_match and relevant variable.
    $route_match = $this->prophesize(RouteMatchInterface::class);
    $route_match->getRouteName()->willReturn('diff.revisions_diff');
    $this->assertTrue($theme->isDiffRoute($route_match->reveal()));
    $route_match->getRouteName()->willReturn('entity.entity_type_id.revisions_diff');
    $this->assertTrue($theme->isDiffRoute($route_match->reveal()));
    $route_match->getParameter('filter')->willReturn('visual_inline');

    $container = $this->prophesize(ContainerInterface::class);
    $container->get('config.factory')->willReturn($this->configFactory);
    $diff_config = $this->prophesize(ImmutableConfig::class);
    $this->configFactory->get('diff.settings')
      ->willReturn($diff_config->reveal());
    $diff_config->get('general_settings.visual_inline_theme')->willReturn('default');
    \Drupal::setContainer($container->reveal());

    // Check if applies function returns true.
    $this->assertTrue($theme->applies($route_match->reveal()));
  }

}
