<?php

namespace Drupal\Tests\book\Kernel;

use Drupal\book\Form\BookAdminEditForm;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\book\Form\BookAdminEditForm
 * @group legacy
 */
class BookAdminEditFormDeprecationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['book', 'node'];

  /**
   * Tests deprecation of constructing a BookAdminEditForm object without the renderer argument.
   *
   * @covers ::__construct
   */
  public function testBookAdminEditFormConstructorDeprecation(): void {
    $this->expectDeprecation('Calling Drupal\book\Form\BookAdminEditForm::__construct() without the $renderer argument is deprecated in drupal:10.1.0 and will be required in drupal:11.0.0. See https://www.drupal.org/node/2876656');
    new BookAdminEditForm(
      $this->container->get('entity_type.manager')->getStorage('node'),
      $this->container->get('book.manager'),
      $this->container->get('entity.repository')
    );
  }

}
