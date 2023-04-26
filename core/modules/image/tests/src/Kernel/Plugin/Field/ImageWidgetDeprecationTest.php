<?php

namespace Drupal\Tests\image\Kernel\Plugin\Field;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Render\ElementInfoManager;
use Drupal\Core\Render\RendererInterface;
use Drupal\image\Plugin\Field\FieldWidget\ImageWidget;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\image\Plugin\Field\FieldWidget\ImageWidget
 * @group legacy
 */
class ImageWidgetDeprecationTest extends KernelTestBase {

  /**
   * Tests deprecation of constructing an ImageWidget object.
   *
   * - Test constructing a FilterCaption object without the renderer argument.
   * - Test constructing a FilterCaption object without the image_factory
   * argument.
   *
   * @covers ::__construct
   */
  public function testFileWidgetConstructorDeprecation(): void {
    $field_definition_manager = $this->prophesize(FieldDefinitionInterface::class)->reveal();
    $element_info_manager = $this->prophesize(ElementInfoManager::class)->reveal();
    $renderer = $this->prophesize(classOrInterface: RendererInterface::class)->reveal();
    $this->expectDeprecation('Calling Drupal\image\Plugin\Field\FieldWidget\ImageWidget::__construct() without the $renderer argument is deprecated in drupal:10.1.0 and will be required in drupal:11.0.0. See https://www.drupal.org/node/2876656');
    new ImageWidget(
      '',
      '',
      $field_definition_manager,
      [],
      [],
      $element_info_manager
    );

    $this->expectDeprecation('Calling Drupal\image\Plugin\Field\FieldWidget\ImageWidget::__construct() without the $image_factory argument is deprecated in drupal:10.1.0 and will be required in drupal:11.0.0. See https://www.drupal.org/node/2876656');
    new ImageWidget(
      '',
      '',
      $field_definition_manager,
      [],
      [],
      $element_info_manager,
      $renderer
    );
  }

}
