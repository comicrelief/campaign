<?php

namespace Drupal\Tests\jsonapi\Unit\Routing;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\jsonapi\Routing\JsonApiParamEnhancer;
use Drupal\jsonapi\Routing\Param\OffsetPage;
use Drupal\jsonapi\Routing\Param\Filter;
use Drupal\jsonapi\Routing\Param\Sort;
use Drupal\jsonapi\Routing\Routes;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Prophecy\Promise\ReturnPromise;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;


/**
 * Class JsonApiParamEnhancerTest.
 *
 * @package Drupal\jsonapi\Test\Unit
 *
 * @coversDefaultClass \Drupal\jsonapi\Routing\JsonApiParamEnhancer
 * @group jsonapi
 * @group jsonapi_param_enhancer
 */
class JsonApiParamEnhancerTest extends UnitTestCase {

  /**
   * @covers ::applies
   */
  public function testApplies() {
    $object = new JsonApiParamEnhancer($this->prophesize(EntityFieldManagerInterface::class)->reveal());
    $route = $this->prophesize(Route::class);
    $route->getDefault(RouteObjectInterface::CONTROLLER_NAME)->will(new ReturnPromise([Routes::FRONT_CONTROLLER, 'lorem']));

    $this->assertTrue($object->applies($route->reveal()));
    $this->assertFalse($object->applies($route->reveal()));
  }

  /**
   * @covers ::enhance
   */
  public function testEnhanceFilter() {
    $object = new JsonApiParamEnhancer($this->prophesize(EntityFieldManagerInterface::class)->reveal());
    $request = $this->prophesize(Request::class);
    $query = $this->prophesize(ParameterBag::class);
    $query->get('filter')->willReturn(['filed1' => 'lorem']);
    $query->has(Argument::type('string'))->willReturn(FALSE);
    $query->has('filter')->willReturn(TRUE);
    $request->query = $query->reveal();

    $route = $this->prophesize(Route::class);
    $route->getRequirement('_entity_type')->willReturn('dolor');
    $defaults = $object->enhance([
      RouteObjectInterface::ROUTE_OBJECT => $route->reveal()
    ], $request->reveal());
    $this->assertInstanceOf(Filter::class, $defaults['_json_api_params']['filter']);
    $this->assertInstanceOf(OffsetPage::class, $defaults['_json_api_params']['page']);
    $this->assertTrue(empty($defaults['_json_api_params']['sort']));
  }

  /**
   * @covers ::enhance
   */
  public function testEnhancePage() {
    $object = new JsonApiParamEnhancer($this->prophesize(EntityFieldManagerInterface::class)->reveal());
    $request = $this->prophesize(Request::class);
    $query = $this->prophesize(ParameterBag::class);
    $query->get('page')->willReturn(['cursor' => 'lorem']);
    $query->has(Argument::type('string'))->willReturn(FALSE);
    $query->has('page')->willReturn(TRUE);
    $request->query = $query->reveal();

    $defaults = $object->enhance([], $request->reveal());
    $this->assertInstanceOf(OffsetPage::class, $defaults['_json_api_params']['page']);
    $this->assertTrue(empty($defaults['_json_api_params']['filter']));
    $this->assertTrue(empty($defaults['_json_api_params']['sort']));
  }

  /**
   * @covers ::enhance
   */
  public function testEnhanceSort() {
    $object = new JsonApiParamEnhancer($this->prophesize(EntityFieldManagerInterface::class)->reveal());
    $request = $this->prophesize(Request::class);
    $query = $this->prophesize(ParameterBag::class);
    $query->get('sort')->willReturn('-lorem');
    $query->has(Argument::type('string'))->willReturn(FALSE);
    $query->has('sort')->willReturn(TRUE);
    $request->query = $query->reveal();

    $defaults = $object->enhance([], $request->reveal());
    $this->assertInstanceOf(Sort::class, $defaults['_json_api_params']['sort']);
    $this->assertInstanceOf(OffsetPage::class, $defaults['_json_api_params']['page']);
    $this->assertTrue(empty($defaults['_json_api_params']['filter']));
  }

}
