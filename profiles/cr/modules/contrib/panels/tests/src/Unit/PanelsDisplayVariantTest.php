<?php

/**
 * @file
 * Contains \Drupal\Tests\panels\Unit\PanelsDisplayVariantTest.
 */

namespace Drupal\Tests\panels\Unit;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Block\BlockManager;
use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Utility\Token;
use Drupal\layout_plugin\Plugin\Layout\LayoutInterface;
use Drupal\layout_plugin\Plugin\Layout\LayoutPluginManagerInterface;
use Drupal\panels\Plugin\DisplayBuilder\DisplayBuilderManagerInterface;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;

/**
 * @coversDefaultClass \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant
 * @group Panels
 */
class PanelsDisplayVariantTest extends UnitTestCase {

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface
   */
  protected $contextHandler;

  /**
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidGenerator;

  /**
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * @var \Drupal\Core\Block\BlockManager
   */
  protected $blockManager;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * @var \Drupal\panels\Plugin\DisplayBuilder\DisplayBuilderManagerInterface
   */
  protected $builderManager;

  /**
   * @var \Drupal\layout_plugin\Plugin\Layout\LayoutPluginManagerInterface
   */
  protected $layoutManager;

  /**
   * @var \Drupal\layout_plugin\Plugin\Layout\LayoutInterface
   */
  protected $layout;

  /**
   * @var \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant
   */
  protected $variant;

  public function setUp() {
    $this->account = $this->prophesize(AccountInterface::class);
    $this->contextHandler = $this->prophesize(ContextHandlerInterface::class);
    $this->uuidGenerator = $this->prophesize(UuidInterface::class);
    $this->token = $this->prophesize(Token::class);
    $this->blockManager = $this->prophesize(BlockManager::class);
    $this->conditionManager = $this->prophesize(ConditionManager::class);
    $this->moduleHandler = $this->prophesize(ModuleHandlerInterface::class);
    $this->builderManager = $this->prophesize(DisplayBuilderManagerInterface::class);
    $this->layoutManager = $this->prophesize(LayoutPluginManagerInterface::class);
    $this->layout = $this->prophesize(LayoutInterface::class);

    $this->layoutManager
      ->createInstance(Argument::type('string'), Argument::type('array'))
      ->willReturn($this->layout->reveal());

    $this->variant = new PanelsDisplayVariant([], '', [], $this->contextHandler->reveal(), $this->account->reveal(), $this->uuidGenerator->reveal(), $this->token->reveal(), $this->blockManager->reveal(), $this->conditionManager->reveal(), $this->moduleHandler->reveal(), $this->builderManager->reveal(), $this->layoutManager->reveal());
  }

  /**
   * @covers ::submitConfigurationForm
   */
  public function testSubmitConfigurationForm() {
    $values = ['page_title' => "Go hang a salami, I'm a lasagna hog!"];

    $form = [];
    $form_state = (new FormState())->setValues($values);
    $this->variant->submitConfigurationForm($form, $form_state);

    $property = new \ReflectionProperty($this->variant, 'configuration');
    $property->setAccessible(TRUE);
    $this->assertSame($values['page_title'], $property->getValue($this->variant)['page_title']);
  }

  /**
   * @covers ::getLayout
   */
  public function testGetLayout() {
    $this->assertSame($this->layout->reveal(), $this->variant->getLayout());
  }

  /**
   * @covers ::getRegionNames
   */
  public function testGetRegionNames() {
    $region_names = ['Foo', 'Bar', 'Baz'];
    $this->layout->getPluginDefinition()->willReturn([
      'region_names' => $region_names,
    ]);
    $this->assertSame($region_names, $this->variant->getRegionNames());
  }

  /**
   * @covers ::access
   */
  public function testAccessNoBlocksConfigured() {
    $this->assertFalse($this->variant->access());
  }

  /**
   * @covers ::defaultConfiguration
   */
  public function testDefaultConfiguration() {
    $defaults = $this->variant->defaultConfiguration();
    $this->assertSame('', $defaults['layout']);
    $this->assertSame('', $defaults['page_title']);
  }

}
