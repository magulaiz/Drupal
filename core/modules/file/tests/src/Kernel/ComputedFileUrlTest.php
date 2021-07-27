<?php

namespace Drupal\Tests\file\Kernel;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\file\FileInterface;
use Drupal\file\ComputedFileUrl;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\file\ComputedFileUrl
 *
 * @group file
 */
class ComputedFileUrlTest extends KernelTestBase {

  /**
   * The test URL to use.
   *
   * @var string
   */
  protected $testUrl = 'public://druplicon.txt';

  /**
   * @covers ::getValue
   */
  public function testGetValue() {
    $entity = $this->createMock(FileInterface::class);
    $entity->expects($this->any())
      ->method('getFileUri')
      ->willReturn($this->testUrl);

    $parent = $this->createMock(FieldItemInterface::class);
    $parent->expects($this->exactly(2))
      ->method('getEntity')
      ->willReturn($entity);

    $definition = $this->prophesize(DataDefinitionInterface::class);

    $typed_data = new ComputedFileUrl($definition->reveal(), $this->randomMachineName(), $parent);

    $expected = base_path() . $this->siteDirectory . '/files/druplicon.txt';

    $this->assertSame($expected, $typed_data->getValue());
    // Do this a second time to confirm the same value is returned but the value
    // isn't retrieved from the parent entity again.
    $this->assertSame($expected, $typed_data->getValue());
  }

  /**
   * @covers ::setValue
   */
  public function testSetValue() {
    $name = $this->randomMachineName();
    $parent = $this->createMock(FieldItemInterface::class);
    $parent->expects($this->atLeastOnce())
      ->method('onChange')
      ->with($name);

    $definition = $this->prophesize(DataDefinitionInterface::class);
    $typed_data = new ComputedFileUrl($definition->reveal(), $name, $parent);

    // Setting the value explicitly should mean the parent entity is never
    // called into.
    $typed_data->setValue($this->testUrl);

    $this->assertSame($this->testUrl, $typed_data->getValue());
    // Do this a second time to confirm the same value is returned but the value
    // isn't retrieved from the parent entity again.
    $this->assertSame($this->testUrl, $typed_data->getValue());
  }

  /**
   * @covers ::setValue
   */
  public function testSetValueNoNotify() {
    $name = $this->randomMachineName();
    $parent = $this->createMock(FieldItemInterface::class);
    $parent->expects($this->never())
      ->method('onChange')
      ->with($name);

    $definition = $this->prophesize(DataDefinitionInterface::class);
    $typed_data = new ComputedFileUrl($definition->reveal(), $name, $parent);

    // Setting the value should explicitly should mean the parent entity is
    // never called into.
    $typed_data->setValue($this->testUrl, FALSE);

    $this->assertSame($this->testUrl, $typed_data->getValue());
  }

}
