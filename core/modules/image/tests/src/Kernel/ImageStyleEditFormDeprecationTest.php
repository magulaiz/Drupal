<?php

namespace Drupal\Tests\image\Kernel;

use Drupal\image\Form\ImageStyleEditForm;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\image\Form\ImageStyleEditForm
 * @group legacy
 */
class ImageStyleEditFormDeprecationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['image'];

  /**
   * Tests deprecation of constructing an ImageStyleEditForm object without the renderer argument.
   *
   * @covers ::__construct
   */
  public function testImageStyleEditFormConstructorDeprecation(): void {
    $this->expectDeprecation('Calling Drupal\image\Form\ImageStyleEditForm::__construct() without the $renderer argument is deprecated in drupal:10.1.0 and will be required in drupal:11.0.0. See https://www.drupal.org/node/2876656');
    new ImageStyleEditForm(
      $this->container->get('entity_type.manager')->getStorage('image_style'),
      $this->container->get('plugin.manager.image.effect')
    );
  }

}
