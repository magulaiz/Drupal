<?php

namespace Drupal\Tests\filter\Kernel\Plugin\Filter;

use Drupal\filter\Plugin\Filter\FilterCaption;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\filter\Plugin\Filter\FilterCaption
 * @group legacy
 */
class FilterCaptionDeprecationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['filter'];

  /**
   * Tests deprecations of constructing a FilterCaption object.
   *
   * - Test constructing a FilterCaption object without the filter_manager
   * argument.
   * - Test constructing a FilterCaption object without the renderer argument.
   *
   * @covers ::__construct
   */
  public function testFilterCaptionConstructorDeprecation(): void {
    $this->expectDeprecation('Calling Drupal\filter\Plugin\Filter\FilterCaption::__construct() without the $filter_manager argument is deprecated in drupal:10.1.0 and will be required in drupal:11.0.0. See https://www.drupal.org/node/2876656');
    new FilterCaption(
      [],
      '',
      ['provider' => 'test'],
      NULL,
      $this->container->get('renderer')
    );

    $this->expectDeprecation('Calling Drupal\filter\Plugin\Filter\FilterCaption::__construct() without the $renderer argument is deprecated in drupal:10.1.0 and will be required in drupal:11.0.0. See https://www.drupal.org/node/2876656');
    new FilterCaption(
      [],
      '',
      ['provider' => 'test'],
      $this->container->get('plugin.manager.filter')
    );
  }

}
