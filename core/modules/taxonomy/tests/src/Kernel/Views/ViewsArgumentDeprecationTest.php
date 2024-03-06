<?php

declare(strict_types=1);

namespace Drupal\Tests\taxonomy\Kernel\Views;

use Drupal\KernelTests\KernelTestBase;
use Drupal\taxonomy_test\Plugin\views\argument\TaxonomyViewsArgumentTest;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

/**
 * Tests deprecation messages in views argument plugins.
 *
 * @group taxonomy
 */
class ViewsArgumentDeprecationTest extends KernelTestBase {

  use ExpectDeprecationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'taxonomy',
    'taxonomy_test',
    'views',
  ];

  /**
   * Test the deprecation message in ViewsArgument plugin.
   *
   * @group legacy
   */
  public function testDeprecation(): void {
    $this->expectDeprecation('Calling Drupal\taxonomy\Plugin\views\argument\Taxonomy::__construct() without the $entityRepository argument is deprecated in drupal:10.3.0 and will be required in drupal:11.0.0. See https://www.drupal.org/project/drupal/issues/2765297');
    $plugin = \Drupal::service('plugin.manager.views.argument')->createInstance('taxonomy_views_argument_test', []);
    $this->assertInstanceOf(TaxonomyViewsArgumentTest::class, $plugin);
  }

}
