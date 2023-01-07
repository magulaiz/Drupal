<?php

namespace Drupal\Tests\views_ui\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\views_ui\Form\BreakLockForm;

/**
 * @coversDefaultClass \Drupal\views_ui\Form\BreakLockForm
 * @group legacy
 */
class BreakLockFormDeprecationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['views_ui'];

  /**
   * Tests deprecation of constructing a BreakLockForm without the renderer argument.
   *
   * @covers ::__construct
   */
  public function testBreakLockFormConstructorDeprecation(): void {
    $this->expectDeprecation('Calling Drupal\views_ui\Form\BreakLockForm::__construct() without the $renderer argument is deprecated in drupal:10.1.0 and will be required in drupal:11.0.0. See https://www.drupal.org/node/2876656');
    new BreakLockForm(
      $this->container->get('entity_type.manager'),
      $this->container->get('tempstore.shared')
    );
  }

}
