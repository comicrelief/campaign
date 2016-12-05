<?php

namespace Drupal\Tests\jsonapi\Unit\LinkManager;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\jsonapi\LinkManager\LinkManager;
use Drupal\jsonapi\Routing\Param\OffsetPage;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Symfony\Cmf\Component\Routing\ChainRouterInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class LinkManagerTest.
 *
 * @package Drupal\Tests\jsonapi\Unit\LinkManager
 *
 * @coversDefaultClass \Drupal\jsonapi\LinkManager\LinkManager
 *
 * @group jsonapi
 */
class LinkManagerTest extends UnitTestCase {

  /**
   * The SUT.
   *
   * @var \Drupal\jsonapi\LinkManager\LinkManager
   */
  protected $linkManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $router = $this->prophesize(ChainRouterInterface::class);
    $router->matchRequest(Argument::type(Request::class))->willReturn([
      RouteObjectInterface::ROUTE_NAME => 'fake',
      '_raw_variables' => new ParameterBag(['lorem' => 'ipsum']),
    ]);
    $url_generator = $this->prophesize(UrlGeneratorInterface::class);
    $url_generator->generateFromRoute(Argument::cetera())->willReturnArgument(2);
    $this->linkManager = new LinkManager($router->reveal(), $url_generator->reveal());
  }


  /**
   * @covers ::getPagerLinks
   * @dataProvider getPagerLinksProvider
   */
  public function testGetPagerLinks($offset, $size, $has_next_page, array $pages) {
    // Add the extra stuff to the expected query.
    $pages = array_filter($pages);
    $pages = array_map(function ($page) {
      return ['absolute' => TRUE, 'query' => ['page' => $page]];
    }, $pages);

    $request = $this->prophesize(Request::class);
    // Have the request return the desired page parameter.
    $page_param = $this->prophesize(OffsetPage::class);
    $page_param->getOffset()->willReturn($offset);
    $page_param->getSize()->willReturn($size);
    $request->get('_json_api_params')->willReturn(['page' => $page_param->reveal()]);
    $request->query = new ParameterBag();

    $links = $this->linkManager
      ->getPagerLinks($request->reveal(), ['has_next_page' => $has_next_page]);
    $this->assertEquals($pages, $links);
  }

  /**
   * Data provider for testGetPagerLinks
   *
   * @return array
   *   The data for the test method.
   */
  public function getPagerLinksProvider() {
    return [
      [1, 4, TRUE, [
        'first' => ['offset' => 0, 'size' => 4],
        'prev' => ['offset' => 0, 'size' => 4],
        'next' => ['offset' => 5, 'size' => 4],
      ]],
      [6, 4, FALSE, [
        'first' => ['offset' => 0, 'size' => 4],
        'prev' => ['offset' => 2, 'size' => 4],
        'next' => NULL,
      ]],
      [7, 4, FALSE, [
        'first' => ['offset' => 0, 'size' => 4],
        'prev' => ['offset' => 3, 'size' => 4],
        'next' => NULL,
      ]],
      [10, 4, FALSE, [
        'first' => ['offset' => 0, 'size' => 4],
        'prev' => ['offset' => 6, 'size' => 4],
        'next' => NULL,
      ]],
      [5, 4, TRUE, [
        'first' => ['offset' => 0, 'size' => 4],
        'prev' => ['offset' => 1, 'size' => 4],
        'next' => ['offset' => 9, 'size' => 4],
      ]],
      [0, 4, TRUE, [
        'first' => NULL,
        'prev' => NULL,
        'next' => ['offset' => 4, 'size' => 4],
      ]],
      [0, 1, FALSE, [
        'first' => NULL,
        'prev' => NULL,
        'next' => NULL,
      ]],
      [0, 1, FALSE, [
        'first' => NULL,
        'prev' => NULL,
        'next' => NULL,
      ]],
    ];
  }

  /**
   * Test errors.
   *
   * @covers ::getPagerLinks
   * @expectedException \Drupal\jsonapi\Error\SerializableHttpException
   * @dataProvider getPagerLinksErrorProvider
   */
  public function testGetPagerLinksError($offset, $size, $total, array $pages) {
    $this->testGetPagerLinks($offset, $size, $total, $pages);
  }

  /**
   * Data provider for testGetPagerLinksError.
   *
   * @return array
   *   The data for the test method.
   */
  public function getPagerLinksErrorProvider() {
    return [
      [0, -5, FALSE, [
        'first' => NULL,
        'prev' => NULL,
        'last' => NULL,
        'next' => NULL,
      ]],
    ];
  }

  /**
   * @covers ::getRequestLink
   */
  public function testGetRequestLink() {
    $request = $this->prophesize(Request::class);
    // Have the request return the desired page parameter.
    $page_param = $this->prophesize(OffsetPage::class);
    $page_param->getOffset()->willReturn(NULL);
    $page_param->getSize()->willReturn(NULL);
    $request->get('_json_api_params')->willReturn(['page' => $page_param->reveal()]);
    $request->query = new ParameterBag(['amet' => 'pax']);

    $query = $this->linkManager->getRequestLink($request->reveal(), ['dolor' => 'sid']);
    $this->assertEquals([
      'absolute' => TRUE,
      'query' => ['dolor' => 'sid'],
    ], $query);
    // Get the default query from the request object.
    $query = $this->linkManager->getRequestLink($request->reveal());
    $this->assertEquals([
      'absolute' => TRUE,
      'query' => ['amet' => 'pax'],
    ], $query);
  }

}
