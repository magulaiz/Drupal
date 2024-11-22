<?php

namespace Drupal\KernelTests\Core\Pager;

use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group Pager
 *
 * @coversDefaultClass \Drupal\Core\Pager\PagerManager
 */
class PagerManagerTest extends KernelTestBase {

  /**
   * @covers ::getUpdatedParameters
   *
   * @dataProvider providerTestGetUpdatedParameters
   */
  public function testGetUpdatedParameters(array $query_parameters, string $expected_key): void {
    $element = 2;
    $index = 5;
    $request = Request::create('http://example.com', 'GET', $query_parameters);

    /** @var \Symfony\Component\HttpFoundation\RequestStack $request_stack */
    $request_stack = $this->container->get('request_stack');
    $request_stack->push($request);

    /** @var \Drupal\Core\Pager\PagerManagerInterface $pager_manager */
    $pager_manager = $this->container->get('pager.manager');

    $pager_manager->createPager(30, 10, $element);
    $query = $pager_manager->getUpdatedParameters($request->query->all(), $element, $index);

    $this->assertArrayHasKey($expected_key, $query);

    $this->assertEquals(",,$index", $query['page']);
  }

  /**
   * Provides test cases for PagerManagerTest::testGetUpdatedParameters().
   *
   * @return array
   *   An array of test cases, each which the following values:
   *   - Array of elements to pass to the request.
   *   - The expected value returned by
   *     PagerManagerInterface::getUpdatedParameters().
   */
  public function providerTestGetUpdatedParameters(): array {
    return [
      'string_key' => [
        [
          'other' => 'arbitrary',
        ],
        'other',
      ],
      'numeric_key' => [
        [
          '2' => '',
        ],
        '2',
      ],
    ];
  }

  /**
   * @covers ::findPage
   */
  public function testFindPage() {
    $request = Request::create('http://example.com', 'GET', ['page' => '0,10']);

    /** @var \Symfony\Component\HttpFoundation\RequestStack $request_stack */
    $request_stack = $this->container->get('request_stack');
    $request_stack->push($request);

    $pager_manager = $this->container->get('pager.manager');

    $this->assertEquals(10, $pager_manager->findPage(1));
  }

  /**
   * @covers ::getMaxPagerElementId
   *
   * @dataProvider providerTestGetMaxPagerElementId
   */
  public function testGetMaxPagerElementId(array $elements, int $expected_max_element_id): void {
    /** @var \Drupal\Core\Pager\PagerManagerInterface $pager_manager */
    $pager_manager = $this->container->get('pager.manager');

    foreach ($elements as $element) {
      $pager_manager->createPager(30, 10, $element);
    }

    $this->assertEquals($expected_max_element_id, $pager_manager->getMaxPagerElementId());
  }

  /**
   * Provides test cases for PagerManagerTest::testGetMaxPagerElementId().
   *
   * @return array
   *   An array of test cases, each which the following values:
   *   - Array of elements to pass to PagerManager::createPager().
   *   - The expected value returned by PagerManager::getMaxPagerElementId().
   */
  public function providerTestGetMaxPagerElementId(): array {
    return [
      'no_pager' => [[], -1],
      'single_pager' => [[0], 0],
      'multiple_pagers' => [[30, 10, 20], 30],
    ];
  }

}
