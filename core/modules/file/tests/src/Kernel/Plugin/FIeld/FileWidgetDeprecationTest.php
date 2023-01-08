<?php

namespace Drupal\Tests\file\Kernel\Plugin\Field;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Render\ElementInfoManager;
use Drupal\file\Plugin\Field\FieldWidget\FileWidget;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\file\Plugin\Field\FieldWidget\FileWidget
 * @group legacy
 */
class FileWidgetDeprecationTest extends KernelTestBase {

  /**
   * Tests deprecation of constructing a FileWidget object without the renderer argument.
   *
   * @covers ::__construct
   */
  public function testFileWidgetConstructorDeprecation(): void {
    $field_definition_manager = $this->prophesize(FieldDefinitionInterface::class)->reveal();
    $element_info_manager = $this->prophesize(ElementInfoManager::class)->reveal();
    $this->expectDeprecation('Calling Drupal\file\Plugin\Field\FieldWidget\FileWidget::__construct() without the $renderer argument is deprecated in drupal:10.1.0 and will be required in drupal:11.0.0. See https://www.drupal.org/node/2876656');
    new FileWidget(
      '', 
      '',
      $field_definition_manager,
      [],
      [],
      $element_info_manager
    );
  }

}
