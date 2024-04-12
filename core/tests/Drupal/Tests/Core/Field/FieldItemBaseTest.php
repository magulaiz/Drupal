<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\Field;

use Drupal\Tests\UnitTestCase;
use Drupal\Core\TypedData\ComplexDataDefinitionInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Core\Field\FieldItemBase;

/**
 * @coversDefaultClass \Drupal\Core\Field\FieldItemBase
 * @group Field
 */
class FieldItemBaseTest extends UnitTestCase {

  /**
   * Tests calling __get() without values being set.
   *
   * @covers ::__get
   */
  public function testGetWithOutValues(): void {
    $data_definition = $this->prophesize(ComplexDataDefinitionInterface::class);
    $data_definition->getPropertyDefinitions()
      ->shouldBeCalled()
      ->willReturn([]);
    $data_definition->getPropertyDefinition('property-name')
      ->shouldBeCalled()
      ->willReturn($this->prophesize(DataDefinitionInterface::class)->reveal());
    $fieldItem = $this->getMockForAbstractClass(
      FieldItemBase::class,
      // Constructor array.
      [
        $data_definition->reveal(),
        'field-name',
        NULL,
      ],
      '',
      TRUE,
      TRUE,
      TRUE,
      ['get']
    );
    $typed_data = $this->prophesize(TypedDataInterface::class);
    $typed_data->getValue()
      ->shouldBeCalled()
      ->willReturn('property-value');
    $fieldItem->expects($this->once())
      ->method('get')
      ->with('property-name')
      ->will($this->returnValue($typed_data->reveal()));
    $this->assertEquals('property-value', $fieldItem->__get('property-name'));
  }

  /**
   * Tests calling __get() with values being set.
   *
   * @covers ::__get
   */
  public function testGetWithValues(): void {
    $data_definition = $this->prophesize(ComplexDataDefinitionInterface::class);
    $data_definition->getPropertyDefinitions()
      ->shouldBeCalled()
      ->willReturn([]);
    $fieldItem = $this->getMockForAbstractClass(
      FieldItemBase::class,
      // Constructor array.
      [
        $data_definition->reveal(),
        'field-name',
        NULL,
      ]
    );
    $fieldItem->setValue(['property-name' => 'property-value']);
    $this->assertEquals('property-value', $fieldItem->__get('property-name'));
  }

}
