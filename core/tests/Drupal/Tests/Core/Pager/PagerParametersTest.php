<?php

namespace Drupal\Tests\Core\Pager;

use Drupal\Core\Pager\PagerParameters;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @coversDefaultClass \Drupal\Core\Pager\PagerParameters
 * @group Pager
 */
class PagerParametersTest extends UnitTestCase {

  /**
   * @covers ::findPage
   * @dataProvider providePagerQueries
   */
  public function testFindPage($raw_query, $parameter, $query) {
    $request_stack = new RequestStack();
    $request_stack->push(new Request());
    $parameters = new PagerParameters($request_stack);
    $request_stack->getCurrentRequest()->query->set('page', $raw_query);
    foreach ($query as $key => $value) {
      $this->assertSame($value, $parameters->findPage($key));
    }
  }

  /**
   * @covers ::getPagerQuery
   * @dataProvider providePagerQueries
   */
  public function testGetPagerQuery($raw_query, $parameter, $query) {
    $request_stack = new RequestStack();
    $request_stack->push(new Request());
    $parameters = new PagerParameters($request_stack);
    $request_stack->getCurrentRequest()->query->set('page', $raw_query);
    $this->assertEquals($query, $parameters->getPagerQuery());
  }

  /**
   * Ensure missing request is handled cleanly.
   *
   * @covers ::getPagerParameter
   * @dataProvider providePagerQueries
   */
  public function testGetPagerParameterNoRequest($raw_query, $parameter) {
    $request_stack = new RequestStack();
    $parameters = new PagerParameters($request_stack);
    $this->assertSame('', $parameters->getPagerParameter());
  }

  /**
   * @covers ::getPagerParameter
   * @dataProvider providePagerQueries
   */
  public function testGetPagerParameter($raw_query, $parameter) {
    $request_stack = new RequestStack();
    $request_stack->push(new Request());
    $parameters = new PagerParameters($request_stack);
    $request_stack->getCurrentRequest()->query->set('page', $raw_query);
    $this->assertSame($parameter, $parameters->getPagerParameter());
  }

  public function providePagerQueries() {
    return [
      [NULL, '', [0]],
      // Array values aren't supported, so they default to empty.
      [[], '', [0]],
      [[1, 2, 3], '', [0]],
      ['', '', [0]],
      ['0', '0', [0]],
      ['1', '1', [1]],
      [0, '0', [0]],
      [
        '1,2,3,4',
        '1,2,3,4',
        [1, 2, 3, 4],
      ],
      [
        '4,3,2,1',
        '4,3,2,1',
        [4, 3, 2, 1],
      ],
    ];
  }

}
